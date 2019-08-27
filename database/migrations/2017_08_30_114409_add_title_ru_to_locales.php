<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleRuToLocales extends Migration
{
    public function up()
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->string('title_ru')->after('title');
        });
    }

    public function down()
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropColumn('title_ru');
        });
    }
}
