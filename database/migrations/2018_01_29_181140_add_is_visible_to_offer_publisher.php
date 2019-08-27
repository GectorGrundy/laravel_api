<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsVisibleToOfferPublisher extends Migration
{
    public function up()
    {
        Schema::table('offer_publisher', function (Blueprint $table) {
            $table->boolean('can_create_flow');
        });
    }
}
