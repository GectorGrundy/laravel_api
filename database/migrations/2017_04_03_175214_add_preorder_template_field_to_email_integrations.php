<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreorderTemplateFieldToEmailIntegrations extends Migration
{
    public function up()
    {
        Schema::table('email_integrations', function (Blueprint $table) {
            $table->string('preorder_template')->after('mail_driver');
        });
    }

    public function down()
    {
        Schema::table('email_integrations', function (Blueprint $table) {
            $table->dropColumn(['preorder_template']);
        });
    }
}
