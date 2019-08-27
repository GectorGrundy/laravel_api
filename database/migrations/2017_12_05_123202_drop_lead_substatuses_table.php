<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropLeadSubstatusesTable extends Migration
{
    public function up()
    {
        Schema::drop('lead_substatuses');
    }
}
