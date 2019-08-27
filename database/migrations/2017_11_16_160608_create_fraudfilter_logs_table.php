<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFraudfilterLogsTable extends Migration
{
    public function up()
    {
        Schema::create('fraudfilter_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_safepage');
            $table->string('response');
            $table->string('api_key');
            $table->string('campaign_id');
            $table->string('X-FF-P');
            $table->string('X-FF-REMOTE-ADDR');
            $table->string('X-FF-X-FORWARDED-FOR');
            $table->string('X-FF-X-REAL-IP');
            $table->string('X-FF-DEVICE-STOCK-UA');
            $table->string('X-FF-X-OPERAMINI-PHONE-UA');
            $table->string('X-FF-HEROKU-APP-DIR');
            $table->string('X-FF-X-FB-HTTP-ENGINE');
            $table->string('X-FF-X-PURPOSE');
            $table->string('X-FF-REQUEST-SCHEME');
            $table->string('X-FF-CONTEXT-DOCUMENT-ROOT');
            $table->string('X-FF-SCRIPT-FILENAME');
            $table->string('X-FF-REQUEST-URI');
            $table->string('X-FF-SCRIPT-NAME');
            $table->string('X-FF-PHP-SELF');
            $table->string('X-FF-REQUEST-TIME-FLOAT');
            $table->string('X-FF-COOKIE');
            $table->string('X-FF-ACCEPT-ENCODING');
            $table->string('X-FF-ACCEPT-LANGUAGE');
            $table->string('X-FF-CF-CONNECTING-IP');
            $table->string('X-FF-INCAP-CLIENT-IP');
            $table->string('X-FF-QUERY-STRING');
            $table->string('X-FF-ACCEPT');
            $table->string('X-FF-X-WAP-PROFILE');
            $table->string('X-FF-PROFILE');
            $table->string('X-FF-WAP-PROFILE');
            $table->string('X-FF-REFERER');
            $table->string('X-FF-HOST');
            $table->string('X-FF-VIA');
            $table->string('X-FF-CONNECTION');
            $table->string('X-FF-X-REQUESTED-WITH');
            $table->string('User-Agent');
            $table->string('X-FF-HOST-ORDER');
            $table->timestamps();
        });
    }
}
