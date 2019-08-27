<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsHttpsToDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_https')->after('hash');
        });
    }
}
