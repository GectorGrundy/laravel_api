<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferUserGroup extends Migration
{
    public function up()
    {
        Schema::create('offer_user_group', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('user_group_id');
            $table->boolean('can_create_flow');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_user_group');
    }
}
