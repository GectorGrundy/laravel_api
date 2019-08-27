<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadStateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    DeviceStat, Lead, BalanceTransaction, HourlyStat, SystemTransaction, TargetGeoStat
};

class OnLeadTrashed implements ShouldQueue
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

    public function handle(LeadStateEvent $event)
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
                HourlyStat::leadTrash($this->lead);
                DeviceStat::leadTrash($this->lead);
                TargetGeoStat::leadTrash($this->lead);
                break;

            case Lead::APPROVED:
                HourlyStat::leadTrashAfterApproved($this->lead);
                DeviceStat::leadTrashAfterApproved($this->lead);
                TargetGeoStat::leadTrashAfterApproved($this->lead);
                break;

            case Lead::CANCELLED:
                HourlyStat::leadTrashAfterCancelled($this->lead);
                DeviceStat::leadTrashAfterCancelled($this->lead);
                TargetGeoStat::leadTrashAfterCancelled($this->lead);
                break;
        }
    }
}
