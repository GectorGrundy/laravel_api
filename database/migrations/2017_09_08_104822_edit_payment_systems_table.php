<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditPaymentSystemsTable extends Migration
{
    public function up()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            $table->renameColumn('min_rub_payment', 'min_payout');

            $table->dropColumn(['min_usd_payment', 'min_eur_payment', 'created_at', 'updated_at']);

            $table->decimal('comission', 12);
            $table->string('comission_type')->comment('fixed/percentage');
            $table->unsignedSmallInteger('currency_id');
        });
    }

    public function down()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            $table->dropColumn(['comission', 'comission_type']);
            $table->timestamps();
        });
    }
}
