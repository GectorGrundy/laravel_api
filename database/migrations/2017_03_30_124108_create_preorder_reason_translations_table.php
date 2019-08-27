<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreorderReasonTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('preorder_reason_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('locale_id');
            $table->integer('preorder_reason_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('preorder_reason_translations');
    }
}
