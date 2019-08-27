<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsModeratedColumnToFlowFlowWidgetTable extends Migration
{
    public function up()
    {
        Schema::table('flow_flow_widget', function (Blueprint $table) {
            $table->boolean('is_moderated')->default(0)->after('flow_widget_id');
        });
    }
}
