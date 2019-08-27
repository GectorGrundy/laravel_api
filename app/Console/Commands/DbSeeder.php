<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class DbSeeder extends Command
{
    protected $signature = 'db:reset';
    protected $description = '';

    public function handle()
    {
        if (app()->environment('production')) {
            $this->error("App in production. You cant do this ( ͡° ͜ʖ ͡°)");
            return;
        }

        $this->call('migrate:fresh', ['--force' => true]);
        $this->call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        $this->call('cache:clear', ['redis']);
        $this->info("\n ( ͡° ͜ʖ ͡°) \n");
    }
}
