<?php

use App\Models\Lead;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultValueToLeadsField extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('data1', 32)->default('')->change();
            $table->string('data2', 32)->default('')->change();
            $table->string('data3', 32)->default('')->change();
            $table->string('data4', 32)->default('')->change();
            $table->string('status', 10)->default(Lead::NEW)->change();
        });
    }

    public function down()
    {
        //
    }
}
