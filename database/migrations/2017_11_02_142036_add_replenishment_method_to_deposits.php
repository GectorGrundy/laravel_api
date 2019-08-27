<?php

use App\Models\Deposit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReplenishmentMethodToDeposits extends Migration
{
    public function up()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('replenishment_method')->after('currency_id')->default(Deposit::OTHER);
        });
    }
}
