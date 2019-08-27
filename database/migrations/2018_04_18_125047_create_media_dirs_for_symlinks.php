<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaDirsForSymlinks extends Migration
{
    public function up()
    {
        $domains_path = (string)config('env.domains_path');

        if (!File::isDirectory($domains_path . '/apollofiles/')) {
            File::makeDirectory($domains_path . '/apollofiles/');
        }

        if (!File::isDirectory($domains_path . '/apollofiles/prelanding')) {
            \File::makeDirectory($domains_path . '/apollofiles/prelanding');
        }

        if (!File::isDirectory($domains_path . '/apollofiles/landing')) {
            \File::makeDirectory($domains_path . '/apollofiles/landing');
        }
    }
}
