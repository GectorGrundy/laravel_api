<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditPublisherTargetGeo extends Migration
{
    public function up()
    {
        Schema::table('publisher_target_geo', function (Blueprint $table) {
            $table->boolean('is_percentage');
            $table->boolean('is_cpa_convertible');
            $table->decimal('cpa_payout', 12);
            $table->decimal('cpa_profit', 12);
        });
    }
}
