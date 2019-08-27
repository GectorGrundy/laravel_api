<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmIsPercentageFromTargetGeo extends Migration
{
    public function up()
    {
        Schema::table('target_geo', function (Blueprint $table) {
            $table->dropColumn(['is_percentage']);
        });

        Schema::table('publisher_target_geo', function (Blueprint $table) {
            $table->dropColumn(['is_percentage']);
        });
    }
}
