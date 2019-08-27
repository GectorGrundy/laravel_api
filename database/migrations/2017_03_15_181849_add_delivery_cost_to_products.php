<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryCostToProducts extends Migration
{
    public function up()
    {
        Schema::table('products', function ($table) {
            $table->decimal('old_price', 12, 2)->after('price');
            $table->decimal('delivery_cost', 12, 2)->after('old_price');
        });
    }

    public function down()
    {
        Schema::table('products', function ($table) {
            $table->dropColumn(['delivery_cost', 'old_price']);
        });
    }
}
