<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToOfferIdFields extends Migration
{
    public function up()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->index(['offer_id']);
        });
        Schema::table('offer_publisher', function (Blueprint $table) {
            $table->index(['offer_id']);
        });
        Schema::table('offer_user_group', function (Blueprint $table) {
            $table->index(['offer_id']);
        });
    }
}
