<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('ticket_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->text('message');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('ticket_messages');
    }
}
