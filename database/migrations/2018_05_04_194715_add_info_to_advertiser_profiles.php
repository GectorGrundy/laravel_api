<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoToAdvertiserProfiles extends Migration
{
    public function up()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            $table->string('info')->after('whatsapp');
        });
    }
}
