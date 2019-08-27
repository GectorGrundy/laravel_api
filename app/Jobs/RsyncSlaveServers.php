<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use AFM\Rsync\Rsync;

class RsyncSlaveServers implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        $path = $this->path;
        Server::active()->slave()->get()->each(function ($server) use ($path) {

            $rsync = new Rsync([
                'delete_from_target' => true,
                'show_output' => false,
                'ssh' => array(
                    'host' => $server->host,
                    'username' => $server->user,
                    'public_key' => 'we_dont_have_it'
                )
            ]);

            $rsync->sync($path, $path);
        });
    }
}
