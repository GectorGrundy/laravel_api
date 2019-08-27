<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('offer_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('locale_id');
            $table->string('title', 512);
            $table->string('description', 512);
            $table->string('agreement', 512);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_translations');
    }
}
