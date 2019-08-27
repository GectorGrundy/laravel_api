<?php

use App\Models\Lead;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIntegrationIdsInLeads extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('integration_type', 32)->after('integration_id');
        });

        Lead::chunk(1000, function ($leads) {
            foreach ($leads as $lead) {
                $lead->update([
                    'integration_type' => Lead::INTEGRATION,
                ]);
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['payment_method_integration_id']);
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
