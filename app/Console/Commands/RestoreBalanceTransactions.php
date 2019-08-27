<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\{
    Account, BalanceTransaction, Lead
};

class RestoreBalanceTransactions extends Command
{
    protected $signature = 'lead:restore_transactions_and_balances';
    protected $description = 'Restore all balance transactions history by leads table';

    public function handle()
    {
        $this->deleteLeadBalanceTransactions();

        Lead::where('advertiser_id', '!=', 0)->chunk(500, function ($leads) {
            foreach ($leads as $lead) {
                /**
                 * @var Lead $lead
                 */
                $transaction = BalanceTransaction::insertAdvertiserHold($lead);
                $this->updateTransactionCreatedAt($transaction, $lead['created_at']);

                switch ($lead['status']) {

                    case 'approved':
                        $transaction = BalanceTransaction::insertAdvertiserUnhold($lead, Lead::NEW);
                        $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                        $transaction = BalanceTransaction::insertPublisherHold($lead);
                        $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                        $transaction = BalanceTransaction::insertPublisherUnhold($lead);
                        $this->updateTransactionCreatedAt(
                            $transaction,
                            $lead->processed_at->addMinutes($lead['hold_time'])->toDatetimeString()
                        );
                        break;

                    case 'cancelled':
                    case 'trashed':
                        $transaction = BalanceTransaction::insertPublisherCancel($lead, Lead::NEW);
                        $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);

                        $transaction = BalanceTransaction::insertAdvertiserCancel($lead, Lead::NEW);
                        $this->updateTransactionCreatedAt($transaction, $lead['processed_at']);
                        break;
                }
            }
        });

        $this->recountPublisherBalance();
        $this->recountAdvertiserBalance();
    }

    private function deleteLeadBalanceTransactions()
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
            ->delete();
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

    private function recountPublisherBalance()
    {
        $balances = DB::table('balance_transactions')
            ->selectRaw('
                    SUM(CASE WHEN `currency_id` = 1 THEN `balance_sum` ELSE 0 END) as balance_rub,
                    SUM(CASE WHEN `currency_id` = 3 THEN `balance_sum` ELSE 0 END) as balance_usd,
                    SUM(CASE WHEN `currency_id` = 5 THEN `balance_sum` ELSE 0 END) as balance_eur,
                    SUM(CASE WHEN `currency_id` = 1 THEN `hold_sum` ELSE 0 END) as hold_rub,
                    SUM(CASE WHEN `currency_id` = 3 THEN `hold_sum` ELSE 0 END) as hold_usd,
                    SUM(CASE WHEN `currency_id` = 5 THEN `hold_sum` ELSE 0 END) as hold_eur,
                    user_id'
            )
            ->groupBy('user_id')
            ->get();

        foreach ($balances as $data) {
            DB::table('publisher_profiles')
                ->where('user_id', $data->user_id)
                ->update([
                    'balance_rub' => (int)($data->balance_rub ?? 0),
                    'balance_usd' => (int)($data->balance_usd ?? 0),
                    'balance_eur' => (int)($data->balance_eur ?? 0),
                    'hold_rub' => (int)($data->hold_rub ?? 0),
                    'hold_usd' => (int)($data->hold_usd ?? 0),
                    'hold_eur' => (int)($data->hold_eur ?? 0),
                ]);
        }
    }

    private function recountAdvertiserBalance()
    {
        Account::all()->each(function (Account $account) {

            $balances = DB::table('balance_transactions')
                ->selectRaw('
                    SUM(`balance_sum`) as balance_sum,
                    SUM(`system_balance_sum`) as system_balance_sum,
                    SUM(`hold_sum`) as hold_sum,
                    user_id'
                )
                ->where('user_id', $account['user_id'])
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
}
