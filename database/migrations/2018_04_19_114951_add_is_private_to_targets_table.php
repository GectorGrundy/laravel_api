<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPrivateToTargetsTable extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->boolean('is_private')->default(0)->after('landing_type');
        });
    }
}
