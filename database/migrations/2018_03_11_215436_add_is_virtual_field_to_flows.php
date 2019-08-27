<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsVirtualFieldToFlows extends Migration
{
    public function up()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->boolean('is_virtual')->after('title');
        });
    }
}
