<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTargetTemplatesTable extends Migration
{
    public function up()
    {
        Schema::rename('target_translations', 'target_template_translations');

        DB::table('target_template_translations')->truncate();

        Schema::table('target_template_translations', function (Blueprint $table) {
            $table->renameColumn('target_id', 'target_template_id');
        });
    }
}
