<?php
declare(strict_types=1);

use App\Models\Lead;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class NewSystemProfitFieldsToLeads extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('advertiser_payout', 12)->after('payout');
            $table->unsignedSmallInteger('advertiser_currency_id')->after('advertiser_payout');
            $table->timestamp('advertiser_payout_completed_at')->nullable()->after('advertiser_currency_id');
        });

        Lead::with(['target_geo_rule'])->chunk(500, function (Collection $leads) {
            foreach ($leads as $lead) {
                $lead->update([
                    'advertiser_payout' => $lead->target_geo_rule['charge'],
                    'advertiser_currency_id' => $lead->target_geo_rule['currency_id'],
                    'advertiser_payout_completed_at' => $lead['processed_at'],
                ]);
            }
        });
    }
}
