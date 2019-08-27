<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Models\{
    Account, SystemTransaction, User, Lead, TargetGeo, PublisherProfile, BalanceTransaction
};

class RestorePublisherLeadsPayout extends Command
{
    protected $signature = 'lead:restore_publisher_leads_payout';
    protected $description = '';

    public function handle()
    {
        // Обновить сами лиды
        // Перегенерировать транзакции - удалить старые, добавить новые
        // Обновить баланс паблишера
        // Обновить баланс рекла
        // Не обязательно: Обновить системные транзакции
        // @todo Обновлять `publisher_statistics`

        $date_from = '2018-01-01';
        $date_to = '2018-02-09';
        $refresh_system_transactions = true;
        $user_group_id = 2;

        $advertiser_ids = [
//            5, // test
            25, //
            42, //
            44, //
            46, //
        ];

        if ($refresh_system_transactions) {
            SystemTransaction::truncate();
        }

        foreach ($advertiser_ids as $advertiser_id) {

            User::where('group_id', $user_group_id)->get()
                ->each(function (User $publisher) use ($advertiser_id, $date_from, $date_to) {
                    Lead::with(['target_geo_rule', 'advertiser.profile'])
                        ->createdBetweenDates($date_from, $date_to)
                        ->where('advertiser_id', $advertiser_id)
                        ->where('publisher_id', $publisher['id'])->chunk(50, function (Collection $leads) {
                            /**
                             * @var Lead $lead
                             */
                            foreach ($leads as $lead) {
                                $this->updateLead($lead);
                            }
                        });

                    $this->recountPublisherBalance($publisher);
                });

            $this->recountAdvertiserBalance($advertiser_id);

            if ($refresh_system_transactions) {
                $this->generateSystemTransactions($advertiser_id);
            }
        }
    }

    private function updateLead(Lead $lead)
    {
        $target_geo = (new TargetGeo())->getById($lead['target_geo_id'], [], $lead['publisher_id']);

        $this->deleteLeadBalanceTransactions($lead);

        $currency_different = $lead->target_geo_rule['currency_id'] != $target_geo['payout_currency_id'];
        $advertiser_payout_completed_at = null;
        if (!$currency_different) {
            $advertiser_payout_completed_at = $lead['processed_at'];
        }

        $lead->update([
            'advertiser_payout' => $lead->target_geo_rule['charge'],
            'advertiser_currency_id' => $lead->target_geo_rule['currency_id'],
            'advertiser_payout_completed_at' => $advertiser_payout_completed_at,
            'payout' => $target_geo['payout'],
            'currency_id' => $target_geo['payout_currency_id'],
            'profit' => 0,
        ]);

        $transaction = BalanceTransaction::insertAdvertiserHold($lead);
        $this->updateTransactionCreatedAt($transaction, $lead['created_at']);

        switch ($lead['status']) {

            case Lead::APPROVED:
                if ($currency_different) {
                    $lead->advertiser->profile->updateUnpaidLeadsCount(1);
                } else {
                    $this->leadCompleteAutomatically($lead);
                }

                $transaction = BalanceTransaction::insertAdvertiserUnhold($lead, Lead::NEW);
                $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                $transaction = BalanceTransaction::insertPublisherHold($lead);
                $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                $transaction = BalanceTransaction::insertPublisherUnhold($lead);

                $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $lead['processed_at'])
                    ->addMinutes($lead['hold_time'])
                    ->toDateTimeString();

                $this->updateTransactionCreatedAt($transaction, $created_at);
                break;

            case Lead::CANCELLED:
            case Lead::TRASHED:
                $transaction = BalanceTransaction::insertPublisherCancel($lead, Lead::NEW);
                $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                $transaction = BalanceTransaction::insertAdvertiserCancel($lead, Lead::NEW);
                $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);
                break;
        }
    }

    private function leadCompleteAutomatically(Lead $lead): void
    {
        $profit = (float)$lead->target_geo_rule['charge'] - (float)$lead['payout'];

        $lead->complete($profit);
    }

    private function deleteLeadBalanceTransactions(Lead $lead)
    {
        DB::table('balance_transactions')
            ->whereIn('type', [
                BalanceTransaction::ADVERTISER_CANCEL,
                BalanceTransaction::ADVERTISER_HOLD,
                BalanceTransaction::ADVERTISER_UNHOLD,
                BalanceTransaction::PUBLISHER_CANCEL,
                BalanceTransaction::PUBLISHER_HOLD,
                BalanceTransaction::PUBLISHER_UNHOLD,
            ])
            ->where('entity_id', $lead['id'])
            ->where('entity_type', BalanceTransaction::LEAD)
            ->delete();
    }

    private function recountPublisherBalance(User $publisher)
    {
        $balances = DB::table('balance_transactions')
            ->selectRaw('
                    SUM(CASE WHEN `currency_id` = 1 THEN `balance_sum` ELSE 0 END) as balance_rub,
                    SUM(CASE WHEN `currency_id` = 3 THEN `balance_sum` ELSE 0 END) as balance_usd,
                    SUM(CASE WHEN `currency_id` = 5 THEN `balance_sum` ELSE 0 END) as balance_eur,
                    SUM(CASE WHEN `currency_id` = 1 THEN `hold_sum` ELSE 0 END) as hold_rub,
                    SUM(CASE WHEN `currency_id` = 3 THEN `hold_sum` ELSE 0 END) as hold_usd,
                    SUM(CASE WHEN `currency_id` = 5 THEN `hold_sum` ELSE 0 END) as hold_eur'
            )
            ->where('user_id', $publisher['id'])
            ->groupBy('user_id')
            ->first();

        DB::table('publisher_profiles')
            ->where('user_id', $publisher['id'])
            ->update([
                'balance_rub' => (int)($balances->balance_rub ?? 0),
                'balance_usd' => (int)($balances->balance_usd ?? 0),
                'balance_eur' => (int)($balances->balance_eur ?? 0),
                'hold_rub' => (int)($balances->hold_rub ?? 0),
                'hold_usd' => (int)($balances->hold_usd ?? 0),
                'hold_eur' => (int)($balances->hold_eur ?? 0),
            ]);
    }

    private function recountAdvertiserBalance(int $advertiser_id)
    {
        Account::where('user_id', $advertiser_id)->get()->each(function (Account $account) use ($advertiser_id) {

            $balances = DB::table('balance_transactions')
                ->selectRaw('
                    SUM(`balance_sum`) as balance_sum,
                    SUM(`system_balance_sum`) as system_balance_sum,
                    SUM(`hold_sum`) as hold_sum'
                )
                ->where('user_id', $advertiser_id)
                ->where('currency_id', $account['currency_id'])
                ->groupBy('user_id')
                ->first();

            $account->update([
                'balance' => (int)($balances->balance_sum ?? 0),
                'system_balance' => (int)($balances->system_balance_sum ?? 0),
                'hold' => (int)($balances->hold_sum ?? 0),
            ]);
        });
    }

    private function updateTransactionCreatedAt(?BalanceTransaction $transaction, $created_at)
    {
        if (\is_null($transaction)) {
            return null;
        }

        DB::table('balance_transactions')->where('id', $transaction['id'])->update([
            'created_at' => $created_at,
            'updated_at' => $created_at,
        ]);
    }

    private function generateSystemTransactions(int $advertiser_id)
    {
        Lead::where('status', Lead::APPROVED)
            ->whereNotNull('advertiser_payout_completed_at')
            ->where('advertiser_id', $advertiser_id)
            ->get()
            ->each(function (Lead $lead) use ($advertiser_id) {
                $profit = (float)$lead['profit'];

                if ($profit) {
                    SystemTransaction::create([
                        'user_id' => $advertiser_id,
                        'user_role' => 'advertiser',
                        'type' => SystemTransaction::ADVERTISER_PROFIT_TYPE,
                        'sum' => $profit,
                        'currency_id' => $lead['advertiser_currency_id'],
                        'entity_id' => $lead['id'],
                        'entity_type' => SystemTransaction::LEAD_ENTITY_TYPE,
                        'description' => '',
                        'created_at' => $lead['created_at'],
                        'updated_at' => $lead['updated_at'],
                        'profit_at' => $lead['processed_at'],
                    ]);
                }
            });
    }
}
