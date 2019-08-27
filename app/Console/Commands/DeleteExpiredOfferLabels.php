<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredOfferLabels extends Command
{
    protected $signature = 'offer_labels:delete_expired';
    protected $description = '';

    public function handle()
    {
        DB::table('offer_offer_label')
            ->whereNotNull('available_at')
            ->where('available_at', '<=', Carbon::now()->toDateTimeString())
            ->delete();
    }
}
