<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ClearFallbackFlowIdForNewCloakingDomains extends Migration
{
    public function up()
    {
        DB::table('domains')->where('donor_url', '!=', '')->update([
            'fallback_flow_id' => 0
        ]);
    }
}
