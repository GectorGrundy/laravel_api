<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataParameterIdentifiersFieldsToCloakDomainPaths extends Migration
{
    public function up()
    {
        Schema::table('cloak_domain_paths', function (Blueprint $table) {
            $table->char('data_parameter', 5)->after('is_cache_result');
            $table->text('identifiers')->after('data_parameter');
        });
    }
}
