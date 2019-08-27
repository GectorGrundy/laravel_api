<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadIntegrated;
use App\Models\{
    HourlyStat, DeviceStat, BalanceTransaction
};
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OnLeadIntegrated implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'ninja';

    public function handle(LeadIntegrated $event)
    {
        \DB::transaction(function () use ($event) {

            $lead = $event->lead->fresh();

            BalanceTransaction::insertAdvertiserHold($lead);

            (new CreateLeadStatusLog())->handle($event);
        });
    }
}
