<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPolicyCheckboxToPaymentMethodIntegrations extends Migration
{
    public function up()
    {
        Schema::table('payment_method_integrations', function (Blueprint $table) {
            $table->boolean('is_policy_checkbox')->after('is_production');
        });
    }

    public function down()
    {
        Schema::table('payment_method_integrations', function (Blueprint $table) {
            $table->dropColumn(['is_policy_checkbox']);
        });
    }
}
