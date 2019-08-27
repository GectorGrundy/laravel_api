<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoefToTargetGroTable extends Migration
{
    public function up()
    {
        Schema::table('target_geo', function (Blueprint $table) {
            $table->decimal('today_epc', 12, 4)->after('hold_time');
            $table->decimal('today_cr', 7, 4)->after('today_epc');
            $table->decimal('yesterday_epc', 12, 4)->after('today_cr');
            $table->decimal('yesterday_cr', 7, 4)->after('yesterday_epc');
            $table->decimal('week_epc', 12, 4)->after('yesterday_cr');
            $table->decimal('week_cr', 7, 4)->after('week_epc');
            $table->decimal('month_epc', 12, 4)->after('week_cr');
            $table->decimal('month_cr', 7, 4)->after('month_epc');
        });
    }
}
