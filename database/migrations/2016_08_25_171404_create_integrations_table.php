<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
	        $table->string('title');
	        $table->char('internal_api_key', 16);
	        $table->string('add_job_name');
	        $table->string('edit_job_name');
	        $table->json('integration_data');
	        $table->timestamps();
	        $table->softDeletes();

	        $table->index('internal_api_key');
        });
    }

    public function down()
    {
        Schema::drop('integrations');
    }
}
