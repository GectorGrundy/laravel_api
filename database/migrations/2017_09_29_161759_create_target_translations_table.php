<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('target_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('target_id');
            $table->unsignedInteger('locale_id');
            $table->string('title');
        });
    }

    public function down()
    {
        Schema::dropIfExists('target_translations');
    }
}
