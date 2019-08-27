<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropFlowCloakSystemTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('flow_cloak_system');
    }
}
