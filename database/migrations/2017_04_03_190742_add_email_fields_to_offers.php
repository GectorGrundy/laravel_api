<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailFieldsToOffers extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_preorder_email')->after('is_detect_locale');
            $table->boolean('is_first_order_reminder_email')->after('is_preorder_email');
            $table->boolean('is_success_order_email')->after('is_first_order_reminder_email');
        });
    }
}
