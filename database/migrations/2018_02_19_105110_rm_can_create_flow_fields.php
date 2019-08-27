<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmCanCreateFlowFields extends Migration
{
    public function up()
    {
        Schema::table('offer_publisher', function (Blueprint $table) {
            $table->dropColumn(['can_create_flow']);
        });
        Schema::table('offer_user_group', function (Blueprint $table) {
            $table->dropColumn(['can_create_flow']);
        });
    }
}
