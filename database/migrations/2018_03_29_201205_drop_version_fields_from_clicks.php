<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVersionFieldsFromClicks extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn(['browser_version_id', 'os_version_id']);
        });
    }
}
