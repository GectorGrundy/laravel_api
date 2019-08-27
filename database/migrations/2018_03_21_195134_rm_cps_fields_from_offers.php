<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmCpsFieldsFromOffers extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'sms_integration_id', 'is_preorder_email', 'is_first_order_reminder_email', 'is_success_order_email',
                'on_tracking_number_set'
            ]);
        });
    }
}
