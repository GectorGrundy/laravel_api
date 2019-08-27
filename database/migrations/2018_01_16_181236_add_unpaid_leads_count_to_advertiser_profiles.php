<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnpaidLeadsCountToAdvertiserProfiles extends Migration
{
    public function up()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            $table->decimal('rub_system_balance', 12)->after('hold_eur');
            $table->decimal('usd_system_balance', 12)->after('rub_system_balance');
            $table->decimal('eur_system_balance', 12)->after('usd_system_balance');
            $table->unsignedInteger('unpaid_leads_count')->after('eur_system_balance');
        });
    }
}
