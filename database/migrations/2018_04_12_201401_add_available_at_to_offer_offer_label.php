<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAvailableAtToOfferOfferLabel extends Migration
{
    public function up()
    {
        Schema::table('offer_offer_label', function (Blueprint $table) {
            $table->timestamp('available_at')->nullable();
        });
    }
}
