<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\Lead;
use Illuminate\Console\Command;

class RecountHourlyStatLeads extends Command
{
    protected $signature = 'hourly_stat:leads_recount {date_from} {date_to?}';
    protected $description = '';

    public function handle()
    {
        $date_from = $this->argument('date_from');
        $date_to = $this->argument('date_to') ?? Carbon::now()->toDateString();

        $leads = Lead::createdBetweenDates($date_from, $date_to)->get();

        $ids = $leads->pluck('hourly_stat_id')->toArray();

        DB::table('hourly_stat')
            ->whereIn('id', array_unique($ids))
            ->update([
                'held_count' => 0,
                'onhold_payout' => 0,
                'approved_count' => 0,
                'leads_payout' => 0,
                'cancelled_count' => 0,
                'oncancel_payout' => 0,
                'trashed_count' => 0,
                'ontrash_payout' => 0,
                'revshare_payout' => 0,
                'profit' => 0,
            ]);

        foreach ($leads as $lead) {

            switch ($lead['status']) {
                case Lead::NEW:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `held_count`    = (`held_count` + 1),
                                        `onhold_payout` = (`onhold_payout` + {$lead['payout']})
                                    WHERE `id`          = '{$lead['hourly_stat_id']}';"
                    );
                    break;

                case Lead::APPROVED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `approved_count`    = (`approved_count` + 1),
                                        `leads_payout`      = (`leads_payout` + {$lead['payout']}),
                                        `profit`            = (`profit` + {$lead['profit']})
                                    WHERE `id`              = '{$lead['hourly_stat_id']}';"
                    );
                    break;

                case Lead::CANCELLED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `cancelled_count`   = (`cancelled_count` + 1),
                                        `oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
                                    WHERE `id`              = '{$lead['hourly_stat_id']}';"
                    );
                    break;

                case Lead::TRASHED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `trashed_count`   	= (`trashed_count` + 1),
                                        `ontrash_payout`  	= (`ontrash_payout` + {$lead['payout']})
                                    WHERE `id`              = '{$lead['hourly_stat_id']}';"
                    );
                    break;

                default:
                    throw new \BadMethodCallException();
            }
        }
    }
}
