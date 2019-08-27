<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferSourceTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('offer_source_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('offer_source_id');
            $table->unsignedSmallInteger('locale_id');
            $table->string('title');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_source_translations');
    }
}
