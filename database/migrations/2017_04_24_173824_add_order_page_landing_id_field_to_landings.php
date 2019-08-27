<?php

use App\Models\Landing;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderPageLandingIdFieldToLandings extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->unsignedInteger('order_page_landing_id')->after('locale_id');
        });

        Landing::all()->each(function ($landing) {
            $landing->update([
                'order_page_landing_id' => $landing->id
            ]);
        });
    }

    public function down()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropColumn(['order_page_landing_id']);
        });
    }
}
