<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditDescriptionFieldInProductsTable extends Migration
{
    public function up()
    {
	    DB::unprepared('ALTER TABLE `products` 
			CHANGE `description` `description` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;');
    }

    public function down()
    {
	    DB::unprepared('ALTER TABLE `products` 
			CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;');
    }
}
