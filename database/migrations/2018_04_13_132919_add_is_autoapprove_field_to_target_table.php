<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAutoapproveFieldToTargetTable extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->boolean('is_autoapprove')->default(0)->after('is_default');
        });
    }

}
