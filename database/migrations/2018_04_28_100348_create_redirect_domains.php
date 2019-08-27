<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedirectDomains extends Migration
{
    public function up()
    {
        Schema::create('redirect_domains', function (Blueprint $table) {
            $table->tinyIncrements('id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('redirect_domains');
    }
}
