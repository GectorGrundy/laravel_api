<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmOldFieldsInFlowLandingFlowTransitTables extends Migration
{
    public function up()
    {
        Schema::table('flow_landing', function (Blueprint $table) {
            $table->dropColumn(['is_mobile', 'domain_id']);
        });
        Schema::table('flow_transit', function (Blueprint $table) {
            $table->dropColumn(['is_mobile', 'domain_id']);
        });
    }
}
