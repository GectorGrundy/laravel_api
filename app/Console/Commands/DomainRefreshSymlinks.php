<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\SymlinkManager;
use Illuminate\Console\Command;

class DomainRefreshSymlinks extends Command
{
    protected $signature = 'domain:symlink {generate_hosts?}';
    protected $description = 'Refresh symlinks of domains';

    public function handle(SymlinkManager $symlink_manager)
    {
        $hosts = '127.0.0.1';
        $generate_hosts = $this->argument('generate_hosts');

        $domains = Domain::where('realpath', '!=', '')->get();

        \File::cleanDirectory(config('env.domains_path'));

        foreach ($domains as $domain) {
            $symlink = Domain::getSymlink($domain);

            $symlink_manager->create($domain->realpath, $symlink);

            $hosts .= ' ' . $domain->domain;
        }

        if ($generate_hosts === 'true') {
            $this->info($hosts);
        }
    }
}
