<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCloakDomainPathReplacementTable extends Migration
{
    public function up()
    {
        Schema::rename('cloak_domain_path_replacement', 'domain_replacements');

        Schema::table('domain_replacements', function (Blueprint $table) {
            $table->renameColumn('cloak_domain_path_id', 'domain_id');
        });
    }
}
