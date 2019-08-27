<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateUserStatisticSettings extends Migration
{
    public function up()
    {
        DB::table('user_statistic_settings')->get()->each(function ($row) {
            $data = json_decode($row->data, true);
            $geo = $data['columns']['geo'];

            unset($data['columns']['geo'], $data['columns']['source'], $data['columns']['hour']);

            $data['columns']['geo_ip'] = $geo;
            $data['columns']['target_geo'] = $geo;

            DB::table('user_statistic_settings')->where('id', $row->id)->update([
                'data' => json_encode($data)
            ]);
        });
    }
}
