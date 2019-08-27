<?php

use App\Models\Lead;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertApiLeadsToHourlyStat extends Migration
{
    public function up()
    {
        $leads = Lead::where('origin', Lead::API_ORIGIN)->where('hourly_stat_id', 0)->get();

        $hourly_stat_obj = new \App\Models\HourlyStat();

        foreach ($leads as $lead) {

            $hourly_stat = $hourly_stat_obj->getFieldForLead($lead);

            switch ($lead['status']) {
                case Lead::NEW:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `held_count`    = (`held_count` + 1),
                                        `onhold_payout` = (`onhold_payout` + {$lead['payout']})
                                    WHERE `id`          = '{$hourly_stat['id']}';"
                    );
                    break;

                case Lead::APPROVED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `approved_count`    = (`approved_count` + 1),
                                        `leads_payout`      = (`leads_payout` + {$lead['payout']}),
                                        `profit`            = (`profit` + {$lead['profit']})
                                    WHERE `id`              = '{$hourly_stat['id']}';"
                    );
                    break;

                case Lead::CANCELLED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `cancelled_count`   = (`cancelled_count` + 1),
                                        `oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
                                    WHERE `id`              = '{$hourly_stat['id']}';"
                    );
                    break;

                case Lead::TRASHED:
                    DB::statement(
                        "UPDATE `hourly_stat`
                                    SET `trashed_count`   	= (`trashed_count` + 1),
                                        `ontrash_payout`  	= (`ontrash_payout` + {$lead['payout']})
                                    WHERE `id`              = '{$hourly_stat['id']}';"
                    );
                    break;
            }

            $lead->update([
                'hourly_stat_id' => $hourly_stat['id'],
            ]);
        }
    }
}
