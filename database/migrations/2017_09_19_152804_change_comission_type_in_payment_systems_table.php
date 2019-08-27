<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeComissionTypeInPaymentSystemsTable extends Migration
{
    public function up()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            $table->dropColumn('comission_type');
            $table->decimal('fixed_comission', 12)->after('comission');
            $table->renameColumn('comission', 'percentage_comission');
        });
    }

    public function down()
    {

    }
}
