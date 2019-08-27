<?php
declare(strict_types=1);

namespace App\Jobs;

class MoveStaticFile
{
    private $old_filepath;
    private $new_filepath;

    public function __construct($old_filepath, $new_filepath)
    {
        $this->old_filepath = $old_filepath;
        $this->new_filepath = $new_filepath;
    }

    public function handle()
    {
        \File::move($this->old_filepath, $this->new_filepath);

        // @todo Uncomment when it needs
//        $job = new RsyncSlaveServers(public_path('storage'));
//        $job->onQueue(config('queue.app.rsync'));
//        dispatch($job);
    }
}
