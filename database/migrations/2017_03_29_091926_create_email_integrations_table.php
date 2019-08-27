<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('email_integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id');
            $table->boolean('is_active');
            $table->string('title');
            $table->string('mail_from');
            $table->string('mail_sender');
            $table->string('mail_driver');
            $table->string('first_reminder_template')->nullable();
            $table->string('success_template')->nullable();
            $table->jsonb('extra');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('email_integrations');
    }
}
