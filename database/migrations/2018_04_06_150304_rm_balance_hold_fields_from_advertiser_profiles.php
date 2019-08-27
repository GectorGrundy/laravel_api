<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmBalanceHoldFieldsFromAdvertiserProfiles extends Migration
{
    public function up()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'balance_usd', 'hold_usd', 'balance_rub', 'hold_rub', 'balance_eur', 'hold_eur',
                'rub_system_balance', 'usd_system_balance', 'eur_system_balance',
            ]);
        });
    }
}
