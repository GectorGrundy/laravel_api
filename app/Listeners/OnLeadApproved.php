<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadApproved;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\{
    DeviceStat, Lead, BalanceTransaction, HourlyStat, SystemTransaction, TargetGeoStat
};

class OnLeadApproved implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'ninja';

    /**
     * @var string
     */
    private $from_status;
    /**
     * @var Lead $lead
     */
    private $lead;

    public function handle(LeadApproved $event)
    {
        $this->from_status = $event->from_status;
        $this->lead = $event->lead->fresh();

        \DB::transaction(function () use ($event) {

            $this->completeLeadIfPossible();

            BalanceTransaction::insertAdvertiserUnhold($this->lead, $this->from_status);
            BalanceTransaction::insertPublisherHold($this->lead);

            (new CreateLeadStatusLog())->handle($event);
            (new PublisherStatisticUpdateApprovedLeads())->handle($event);
            (new PublisherStatisticUpdatePayout())->handle($event);

            $this->updateHourlyStat();
        });
    }

    private function completeLeadIfPossible()
    {
        if ($this->leadMustBeCompletedManually()) {
            $this->lead->advertiser->profile->updateUnpaidLeadsCount(1);
        } else {
            $this->completeAutomatically();
        }
    }

    /**
     * Проверка, что валюты цены лида для рекла и паблишера не совпадают
     * @return bool
     */
    private function leadMustBeCompletedManually(): bool
    {
        return $this->lead['advertiser_currency_id'] !== $this->lead['currency_id'];
    }

    /**
     * Автоматическое закрытие лида по финансам со стороны рекламодателя
     */
    private function completeAutomatically(): void
    {
        $profit = (float)$this->lead->target_geo_rule['charge'] - (float)$this->lead['payout'];

        $this->lead->complete($profit);

        SystemTransaction::createProfit($this->lead, $profit);
    }


    private function updateHourlyStat()
    {
        switch ($this->from_status) {
            case Lead::NEW:
                HourlyStat::leadApprove($this->lead);
                DeviceStat::leadApprove($this->lead);
                TargetGeoStat::leadApprove($this->lead);
                break;

            case Lead::CANCELLED:
                HourlyStat::leadApproveAfterCancelled($this->lead);
                DeviceStat::leadApproveAfterCancelled($this->lead);
                TargetGeoStat::leadApproveAfterCancelled($this->lead);
                break;

            case Lead::TRASHED:
                HourlyStat::leadApproveAfterTrashed($this->lead);
                DeviceStat::leadApproveAfterTrashed($this->lead);
                TargetGeoStat::leadApproveAfterTrashed($this->lead);
                break;
        }
    }
}
