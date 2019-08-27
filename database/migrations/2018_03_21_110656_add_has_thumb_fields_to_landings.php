<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasThumbFieldsToLandings extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->boolean('has_thumb')->after('is_external');
        });

        DB::table('landings')->update([
            'has_thumb' => 1,
        ]);
    }
}
