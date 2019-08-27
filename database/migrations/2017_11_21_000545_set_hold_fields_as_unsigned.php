<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetHoldFieldsAsUnsigned extends Migration
{
    public function up()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            $table->decimal('hold_rub', 12)->unsigned()->change();
            $table->decimal('hold_usd', 12)->unsigned()->change();
            $table->decimal('hold_eur', 12)->unsigned()->change();
        });
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->decimal('hold_rub', 12)->unsigned()->change();
            $table->decimal('hold_usd', 12)->unsigned()->change();
            $table->decimal('hold_eur', 12)->unsigned()->change();
        });
    }
}
