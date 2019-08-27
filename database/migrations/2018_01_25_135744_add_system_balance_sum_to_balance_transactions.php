<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemBalanceSumToBalanceTransactions extends Migration
{
    public function up()
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->decimal('system_balance_sum', 12)->after('balance_sum');
        });
    }
}
