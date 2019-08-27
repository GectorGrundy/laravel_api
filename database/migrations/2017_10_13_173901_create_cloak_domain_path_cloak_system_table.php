<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloakDomainPathCloakSystemTable extends Migration
{
    public function up()
    {
        Schema::create('cloak_domain_path_cloak_system', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->char('hash', 8);
            $table->unsignedInteger('cloak_domain_path_id');
            $table->unsignedSmallInteger('cloak_system_id');
            $table->boolean('is_cache_result');
            $table->jsonb('attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_cloak_system');
    }
}
