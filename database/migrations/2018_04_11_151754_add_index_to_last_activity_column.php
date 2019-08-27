<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToLastActivityColumn extends Migration
{
    public function up()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->index('last_activity');
        });
    }
}
