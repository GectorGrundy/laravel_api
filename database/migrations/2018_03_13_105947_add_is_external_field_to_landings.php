<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsExternalFieldToLandings extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->boolean('is_external')->after('is_custom_success');
            $table->dropColumn(['order_page_landing_id']);
        });
    }
}
