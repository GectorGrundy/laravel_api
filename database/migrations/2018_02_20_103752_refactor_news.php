<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorNews extends Migration
{
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['type_id']);
            $table->string('type')->after('author_id');
        });

        Schema::dropIfExists('news_types');
    }
}
