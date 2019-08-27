<?php
declare(strict_types=1);

namespace App\Services;

use App\Jobs\RsyncSlaveServers;
use Symfony\Component\Process\Process;

class SymlinkManager
{
    public function create(string $realpath, string $symlink)
    {
        if (file_exists($symlink)) {
            return;
        }

        (new Process("ln -s {$realpath} {$symlink}"))->run();

        $this->generateRsyncJob();
    }

    public function delete($symlink)
    {
        (new Process("rm {$symlink}"))->run();

        $this->generateRsyncJob();
    }

    private function generateRsyncJob()
    {
        // @todo Uncomment when it needs
//        $job = new RsyncSlaveServers(config('env.domains_path'));
//        $job->onQueue(config('queue.app.rsync'));
//        dispatch($job);
    }
}