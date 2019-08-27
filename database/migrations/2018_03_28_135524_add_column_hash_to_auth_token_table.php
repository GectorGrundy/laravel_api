<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnHashToAuthTokenTable extends Migration
{

    public function up()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->char('hash', 8)->after('id');
        });

        $auth_tokens = \App\Models\AuthToken::all();

        foreach ($auth_tokens as $auth_token) {
            $auth_token->hash = Hashids::connection('main')->encode($auth_token['id']);
            $auth_token->save();
        }
    }
}
