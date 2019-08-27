<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveFieldToDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_active')->after('realpath')->default(1);
        });
    }
}
