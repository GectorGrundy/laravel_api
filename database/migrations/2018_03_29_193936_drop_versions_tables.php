<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVersionsTables extends Migration
{
    public function up()
    {
        Schema::dropIfExists('os_versions');
        Schema::dropIfExists('browser_versions');
    }
}
