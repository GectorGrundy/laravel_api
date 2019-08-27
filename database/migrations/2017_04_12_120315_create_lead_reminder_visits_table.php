<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadReminderVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('lead_reminder_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id');
            $table->string('origin', 32);
            $table->string('ip', 16);
            $table->string('user_agent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('lead_reminder_visits');
    }
}
