<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTypeFieldInDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->renameColumn('type', 'entity_type');
        });
    }

    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->renameColumn('entity_type', 'type');
        });
    }
}
