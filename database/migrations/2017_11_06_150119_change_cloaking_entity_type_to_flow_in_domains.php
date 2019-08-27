<?php

use App\Models\Domain;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCloakingEntityTypeToFlowInDomains extends Migration
{
    public function up()
    {
        Schema::rename('cloaking_domains', 'flow_domains');

        DB::table('domains')->where('entity_type', 'cloaking')->update([
            'entity_type' => Domain::FLOW_ENTITY_TYPE
        ]);
    }
}
