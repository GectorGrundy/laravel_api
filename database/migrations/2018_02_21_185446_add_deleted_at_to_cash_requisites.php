<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToCashRequisites extends Migration
{
    public function up()
    {
        Schema::table('cash_requisites', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
}
