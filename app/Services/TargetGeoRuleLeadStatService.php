<?php
declare(strict_types=1);

namespace App\Services;

use DB;
use Illuminate\Database\QueryException;

class TargetGeoRuleLeadStatService
{
    public function insert(int $target_geo_rule_id)
    {
        DB::insert(
            'INSERT INTO `target_geo_rule_leads_stat`
                            SET `target_geo_rule_id`  = :target_geo_rule_id,
                                `leads_count`         = :leads_count,
                                `date`                = :date
                            ON DUPLICATE KEY UPDATE
				                `leads_count`         = (`leads_count` + :upd_leads_count)',
            [
                'target_geo_rule_id' => $target_geo_rule_id,
                'date' => date('Y-m-d', time()),
                'leads_count' => 1,
                'upd_leads_count' => 1,
            ]);
    }

    public function reset(array $target_geo_rule_ids, string $date)
    {
        DB::table('target_geo_rule_leads_stat')
            ->whereIn('target_geo_rule_id', $target_geo_rule_ids)
            ->where('date', $date)
            ->delete();
    }
}
