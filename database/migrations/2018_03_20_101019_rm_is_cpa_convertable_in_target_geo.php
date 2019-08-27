<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmIsCpaConvertableInTargetGeo extends Migration
{
    public function up()
    {
        Schema::table('target_geo', function (Blueprint $table) {
            $table->dropColumn(['is_cpa_convertible', 'cpa_payout', 'has_regions', 'profit', 'cpa_profit']);
        });
        Schema::table('publisher_target_geo', function (Blueprint $table) {
            $table->dropColumn(['is_cpa_convertible', 'cpa_payout', 'profit', 'cpa_profit']);
        });
    }
}
