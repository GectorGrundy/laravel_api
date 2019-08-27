<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Listeners\UnholdLead;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Lead;

class UnholdLeads extends Command
{
    protected $signature = 'leads:unhold';
    protected $description = '';

    public function handle()
    {
        Lead::approved()
            ->where('is_hold', 1)
            ->where('hold_at', '<=', Carbon::now()->toDateTimeString())
            ->chunk(50, function ($leads) {
                foreach ($leads as $lead) {
                    dispatch(new UnholdLead($lead));
                }
            });
    }
}
