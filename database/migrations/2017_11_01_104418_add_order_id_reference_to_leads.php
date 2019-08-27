<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdReferenceToLeads extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->on('orders');
        });
    }
}
