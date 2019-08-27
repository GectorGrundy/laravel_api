<?php
declare(strict_types=1);

use App\Models\TargetGeoRule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChargeCurrencyIdToTargetGeoRules extends Migration
{
    public function up()
    {
        Schema::table('target_geo_rules', function (Blueprint $table) {
            $table->decimal('charge', 12)->after('advertiser_id');
            $table->unsignedSmallInteger('currency_id')->after('charge');
        });

        TargetGeoRule::with(['target_geo'])->get()->each(function (TargetGeoRule $rule) {
            $rule->update([
                'charge' => (float)$rule->target_geo['payout'] + (float)$rule->target_geo['profit'],
                'currency_id' => $rule->target_geo['payout_currency_id'],
            ]);
        });
    }
}
