<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiFieldsToPublisherProfile extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function ($table) {
            $table->char('api_key', 16)->after('hold_eur');
            $table->tinyInteger('is_api_enabled')->unsigned()->after('api_key')->default(1);

            $table->index(['api_key']);
        });
    }
}
