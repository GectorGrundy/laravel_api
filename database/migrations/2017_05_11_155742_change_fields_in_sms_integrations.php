<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldsInSmsIntegrations extends Migration
{
    public function up()
    {
        DB::unprepared(
            'ALTER TABLE `sms_integrations`
                      DROP `offer_id`,
                      DROP `on_tracking_number_set`,
                      CHANGE `offer_title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                      ADD `info` VARCHAR(255) NOT NULL AFTER `title`,
                      ADD `internal_api_key` CHAR(16) NOT NULL AFTER `info`;'
        );
    }
}
