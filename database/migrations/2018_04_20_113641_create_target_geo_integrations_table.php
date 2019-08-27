<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('target_geo_integrations', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->unsignedInteger('advertiser_id');
            $table->unsignedInteger('target_geo_id');
            $table->decimal('charge', 12);
            $table->unsignedTinyInteger('currency_id');
            $table->string('integration_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('target_geo_integrations');
    }
}
