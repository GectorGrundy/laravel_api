<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoWidgetFieldsToFlowsTable extends Migration
{
    public function up()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->string('back_action_sec')->after('tb_url');
            $table->string('back_call_btn_sec')->after('back_action_sec');
            $table->string('back_call_form_sec')->after('back_call_btn_sec');
            $table->string('vibrate_on_mobile_sec')->after('back_call_form_sec');
        });
    }
}
