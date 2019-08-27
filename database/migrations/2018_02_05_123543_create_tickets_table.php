<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('last_message_user_id');
            $table->string('last_message_user_type');
            $table->unsignedInteger('responsible_user_id');
            $table->string('status');
            $table->string('title');
            $table->unsignedSmallInteger('admin_messages_count');
            $table->boolean('is_read_user');
            $table->boolean('is_read_admin');
            $table->timestamp('deferred_until_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
