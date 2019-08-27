<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteCurrencyFromOfferTable extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('currency_id');
            $table->dropColumn('today_epc');
            $table->dropColumn('yesterday_epc');
            $table->dropColumn('week_epc');
            $table->dropColumn('month_epc');
            $table->dropColumn('today_cr');
            $table->dropColumn('yesterday_cr');
            $table->dropColumn('week_cr');
            $table->dropColumn('month_cr');
        });
    }
}
