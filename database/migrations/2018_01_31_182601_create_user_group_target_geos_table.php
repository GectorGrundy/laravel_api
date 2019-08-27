<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTargetGeosTable extends Migration
{
    public function up()
    {
        Schema::create('user_group_target_geo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('target_geo_id');
            $table->unsignedSmallInteger('currency_id');
            $table->decimal('payout', 12);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_group_target_geos');
    }
}
