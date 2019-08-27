<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloakDomainPathsTable extends Migration
{
    public function up()
    {
        Schema::create('cloak_domain_paths', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('domain_id');
            $table->string('path', 256)->index();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cloak_domain_paths');
    }
}
