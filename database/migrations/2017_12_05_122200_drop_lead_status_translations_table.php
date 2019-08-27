<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropLeadStatusTranslationsTable extends Migration
{
    public function up()
    {
        Schema::drop('lead_substatus_translations');
    }
}
