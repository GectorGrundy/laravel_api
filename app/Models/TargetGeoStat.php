<?php
declare(strict_types=1);

namespace App\Models;

use DB;

class TargetGeoStat extends AbstractEntity
{
    protected $table = 'target_geo_stats';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function target_geo()
    {
        return $this->belongsTo(TargetGeo::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getFieldForLead(Lead $lead): self
    {
        $datetime = date('Y-m-d H:00:00', strtotime($lead['created_at']));

        return self::where('datetime', $datetime)
            ->where('target_geo_id', $lead['target_geo_id'])
            ->where('currency_id', $lead['currency_id'])
            ->firstOrCreate([
                'datetime' => $datetime,
                'target_geo_id' => $lead['target_geo_id'],
                'currency_id' => $lead['currency_id'],
            ]);
    }

    public static function onLeadCreated(Lead $lead)
    {
        $target_geo_stat = (new self)->getFieldForLead($lead);

        DB::statement(
            "UPDATE `target_geo_stats`
                        SET `held_count`    = (`held_count` + 1),
                            `onhold_payout` = (`onhold_payout` + {$lead['payout']})
                        WHERE `id`          = '{$target_geo_stat['id']}';"
        );

        $lead->target_geo_stat_id = $target_geo_stat['id'];
        $lead->save();
    }

    public static function leadApprove(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadApproveAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `cancelled_count`   = (`cancelled_count` - 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadApproveAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `trashed_count`     = (`trashed_count` - 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `approved_count`    = (`approved_count` - 1),
					`leads_payout`      = (`leads_payout` - {$lead['payout']}),
					`profit`            = (`profit` - {$lead['profit']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `trashed_count`   = (`trashed_count`  - 1),
					`ontrash_payout`  = (`ontrash_payout` - {$lead['payout']}),
					`cancelled_count` = (`cancelled_count` + 1),
					`oncancel_payout` = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadTrash(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`trashed_count`   	= (`trashed_count` + 1),
					`ontrash_payout`  	= (`ontrash_payout` + {$lead['payout']})
				WHERE `id`              = {$lead['target_geo_stat_id']};"
        );
    }

    public static function leadTrashAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `cancelled_count` = (`cancelled_count` - 1),
					`oncancel_payout` = (`oncancel_payout` - {$lead['payout']}),
					`trashed_count`   = (`trashed_count` + 1),
					`ontrash_payout`  = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadTrashAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']}),
					`trashed_count`    = (`trashed_count` + 1),
					`ontrash_payout`   = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`             = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadRevertedAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']})
				WHERE `id`             = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadRevertedAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `cancelled_count`   = (`cancelled_count`- 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    public static function leadRevertedAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `target_geo_stats`
				SET `trashed_count`     = (`trashed_count`- 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['target_geo_stat_id']}';"
        );
    }

    private static function isLeadValid(Lead $lead)
    {
        return !empty($lead['target_geo_stat_id']);
    }
}