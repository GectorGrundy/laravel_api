<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoWidgetFieldsToLandingsTable extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->boolean('is_back_action')->after('is_external');
            $table->boolean('is_back_call')->after('is_back_action');
            $table->boolean('is_vibrate_on_mobile')->after('is_back_call');
        });
    }
}
