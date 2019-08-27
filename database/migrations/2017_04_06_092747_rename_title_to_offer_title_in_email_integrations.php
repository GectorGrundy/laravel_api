<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTitleToOfferTitleInEmailIntegrations extends Migration
{
    public function up()
    {
       DB::unprepared('ALTER TABLE `email_integrations` 
          CHANGE `title` `offer_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;');
    }

    public function down()
    {
        DB::unprepared('ALTER TABLE `email_integrations` 
          CHANGE `offer_title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;');
    }
}
