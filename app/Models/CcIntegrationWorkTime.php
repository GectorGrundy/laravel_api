<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class CcIntegrationWorkTime extends AbstractEntity
{
    protected $fillable = ['integration_id', 'day', 'hour'];
    protected $table = 'cc_integration_work_time';

    public function syncWorkdate(int $integration_id, array $dates)
    {
        DB::table('cc_integration_work_time')->where('integration_id', $integration_id)->delete();

        $for_query = [];
        $date = date('Y-m-d H:i:s', time());
        foreach ($dates as $day => $hours) {
            if (is_array($hours) && count($hours) > 0) {

                foreach ($hours as $hour) {
                    $for_query[] = [
                        'integration_id' => $integration_id,
                        'day' => $day,
                        'hour' => $hour,
                        self::CREATED_AT => $date,
                        self::UPDATED_AT => $date,
                    ];
                }
            }
        }

        if (count($for_query) > 0) {
            DB::table('cc_integration_work_time')->insert($for_query);
        }
    }
}
