<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataTypeFieldToPublisherProfiles extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->char('data_type', 4)->default('data');
        });
    }
}
