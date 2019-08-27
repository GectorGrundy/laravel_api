<?php

use App\Models\Lead;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHourlyStatIdToLeads extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('hourly_stat_id')->after('device_type_id');
        });

        $houtly_stat = new \App\Models\HourlyStat();
        $leads = Lead::all();

        foreach ($leads as $lead) {
            $lead->hourly_stat_id = $houtly_stat->getFieldForLead($lead)['id'];
            $lead->save();
        }
    }

    public function down()
    {
        //
    }
}
