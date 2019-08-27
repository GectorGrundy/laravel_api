<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBackactionAndIsBackcallColumnsToLeadTable extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('is_back_action')->after('is_test');
            $table->boolean('is_back_call')->after('is_back_action');
        });
    }
}
