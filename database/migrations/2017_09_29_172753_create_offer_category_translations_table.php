<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferCategoryTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('offer_category_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('offer_category_id');
            $table->unsignedSmallInteger('locale_id');
            $table->string('title');
        });
    }

    public function down()
    {
        //
    }
}
