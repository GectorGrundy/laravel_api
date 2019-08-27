<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadReverted;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\{
    DeviceStat, Lead, BalanceTransaction, HourlyStat, SystemTransaction, TargetGeoStat
};

class OnLeadReverted implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'ninja';

    /**
     * @var string
     */
    private $from_status;
    /**
     * @var Lead
     */
    private $lead;

    public function handle(LeadReverted $event)
    {
        $this->from_status = $event->from_status;
        $this->lead = $event->lead->fresh();

        \DB::transaction(function () use ($event) {

            BalanceTransaction::insertAdvertiserCancel($this->lead, $this->from_status);
            BalanceTransaction::insertPublisherCancel($this->lead, $this->from_status);

            if ($this->lead->onHold()) {
                $this->lead->unhold();
            }

            if ($this->from_status === Lead::APPROVED) {
                SystemTransaction::cancelLeadProfit($this->lead);

                (new PublisherStatisticUpdateApprovedLeads())->handle($event);
                (new PublisherStatisticUpdatePayout())->handle($event);
            }

            $this->revertStat();

            HourlyStat::onLeadCreated($this->lead);
            DeviceStat::onLeadCreated($this->lead);
            TargetGeoStat::onLeadCreated($this->lead);
        });
    }

    private function revertStat()
    {
        switch ($this->from_status) {
            case Lead::APPROVED:
                HourlyStat::leadRevertedAfterApproved($this->lead);
                DeviceStat::leadRevertedAfterApproved($this->lead);
                TargetGeoStat::leadRevertedAfterApproved($this->lead);
                break;

            case Lead::CANCELLED:
                HourlyStat::leadRevertedAfterCancelled($this->lead);
                DeviceStat::leadRevertedAfterCancelled($this->lead);
                TargetGeoStat::leadRevertedAfterCancelled($this->lead);
                break;

            case Lead::TRASHED:
                HourlyStat::leadRevertedAfterTrashed($this->lead);
                DeviceStat::leadRevertedAfterTrashed($this->lead);
                TargetGeoStat::leadRevertedAfterTrashed($this->lead);
                break;
        }
    }
}
