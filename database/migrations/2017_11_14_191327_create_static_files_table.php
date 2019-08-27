<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaticFilesTable extends Migration
{
    public function up()
    {
        Schema::create('static_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entity_type', 8);
            $table->unsignedInteger('entity_id');
            $table->string('path');
            $table->string('info');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `static_files` ADD `content` LONGBLOB NOT NULL AFTER `path`;');
    }

    public function down()
    {
        Schema::dropIfExists('static_files');
    }
}
