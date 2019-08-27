<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    DeviceStat, Lead, BalanceTransaction, HourlyStat, SystemTransaction, TargetGeoStat
};

class OnLeadCancelled implements ShouldQueue
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

    public function handle(LeadCancelled $event)
    {
        $this->from_status = $event->from_status;
        $this->lead = $event->lead->fresh();

        \DB::transaction(function () use ($event) {

            BalanceTransaction::insertAdvertiserCancel($this->lead, $this->from_status);
            BalanceTransaction::insertPublisherCancel($this->lead, $this->from_status);

            if ($this->lead->onHold()) {
                $this->lead->unhold();
            }

            $this->updateHourlyStat();

            if ($this->from_status === Lead::APPROVED) {
                SystemTransaction::cancelLeadProfit($this->lead);

                (new PublisherStatisticUpdateApprovedLeads())->handle($event);
                (new PublisherStatisticUpdatePayout())->handle($event);
            }

            (new CreateLeadStatusLog)->handle($event);
        });
    }

    private function updateHourlyStat()
    {
        switch ($this->from_status) {
            case Lead::NEW:
                HourlyStat::leadCancelled($this->lead);
                DeviceStat::leadCancelled($this->lead);
                TargetGeoStat::leadCancelled($this->lead);
                break;

            case Lead::APPROVED:
                HourlyStat::updateOnLeadCancelAfterApproved($this->lead);
                DeviceStat::updateOnLeadCancelAfterApproved($this->lead);
                TargetGeoStat::updateOnLeadCancelAfterApproved($this->lead);
                break;

            case Lead::TRASHED:
                HourlyStat::updateOnLeadCancelAfterTrashed($this->lead);
                DeviceStat::updateOnLeadCancelAfterTrashed($this->lead);
                TargetGeoStat::updateOnLeadCancelAfterTrashed($this->lead);
                break;
        }
    }
}
