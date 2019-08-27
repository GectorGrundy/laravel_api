<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmTitleFieldFromTargets extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn(['title']);
        });
    }
}
