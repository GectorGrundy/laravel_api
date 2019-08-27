<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIsTemplateEngineFromTransits extends Migration
{
    public function up()
    {
        Schema::table('transits', function (Blueprint $table) {
            $table->dropColumn('is_template_engine');
        });
    }

    public function down()
    {
        Schema::table('transits', function (Blueprint $table) {
            $table->unsignedInteger('is_template_engine');
        });
    }
}
