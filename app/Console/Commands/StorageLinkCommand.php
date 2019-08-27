<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SymlinkManager;
use Illuminate\Console\Command;

class StorageLinkCommand extends Command
{
    protected $signature = 'storage:link';
    protected $description = 'Creating a symbolic link';

    public function handle(SymlinkManager $symlink_manager)
    {
        // "public/storage" to "storage/app/public"
        if (file_exists(public_path('storage'))) {
            $this->info('The "public/storage" directory already exists.');
        } else {
            $symlink_manager->create(
                storage_path('app/public'), public_path('storage')
            );

            $this->info('The [public/storage] directory has been linked.');
        }

        // public/static folder to "landings.affninja/static"
        if (file_exists(public_path('static'))) {
            $this->info('The "public/static" directory already exists.');
        } else {
            $symlink_manager->create(
                (string)config('env.landings_path'), public_path('static')
            );

            $this->info('The [public/static] directory has been linked.');
        }
    }
}
