<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RmPaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_method_integrations');
        Schema::dropIfExists('payment_method_templates');
        Schema::dropIfExists('products');
        Schema::dropIfExists('preorders');
        Schema::dropIfExists('preorder_reason_translations');
        Schema::dropIfExists('offer_preorder_reason');
        Schema::dropIfExists('preorder_reasons');
        Schema::dropIfExists('lead_reminder_visits');
    }
}
