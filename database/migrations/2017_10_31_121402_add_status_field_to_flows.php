<?php

use App\Models\Flow;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusFieldToFlows extends Migration
{
    public function up()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->string('status')->default(Flow::DRAFT)->after('hash');
        });

        DB::table('flows')->update(['status' => Flow::ACTIVE]);
    }

    public function down()
    {
        //
    }
}
