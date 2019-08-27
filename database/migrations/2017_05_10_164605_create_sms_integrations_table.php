<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('sms_integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id');
            $table->boolean('is_active');
            $table->string('offer_title');
            $table->boolean('on_tracking_number_set');
            $table->jsonb('extra');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('sms_integrations');
    }
}
