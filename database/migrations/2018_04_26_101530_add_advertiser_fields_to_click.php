<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdvertiserFieldsToClick extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->unsignedMediumInteger('advertiser_id')->after('id');
            $table->decimal('advertiser_payout', 12)->after('advertiser_id');
            $table->unsignedTinyInteger('advertiser_currency_id')->after('advertiser_payout');
        });
    }
}
