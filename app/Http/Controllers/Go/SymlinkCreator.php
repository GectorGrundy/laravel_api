<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Models\{
    Landing, Traits\StaticFileValidator, Transit
};
use App\Models\AbstractEntity;
use App\Classes\LandingHandler;
use App\Services\SymlinkManager;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SymlinkCreator extends Controller
{
    use StaticFileValidator;

    /**
     * @var SymlinkManager
     */
    private $symlink_manager;
    /**
     * @var LandingHandler
     */
    private $landing_handler;

    public function __construct(SymlinkManager $symlink_manager, LandingHandler $landing_handler)
    {
        $this->symlink_manager = $symlink_manager;
        $this->landing_handler = $landing_handler;
    }

    public function __invoke($site_type, $site_hash, $filepath = null)
    {
        try {
            $site = $this->resolveEntity($site_hash, $site_type);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        if (\is_null($filepath)) {
            return redirect($site->system_domain['host']);
        }

        $symlink = $this->getSymlink($site_type, $site_hash);

        if (!file_exists($symlink)) {
            $this->symlink_manager->create($site->system_domain->realpath, $symlink);
        }
        try {
            $filepath = (string)config('env.domains_path') . $_SERVER['REQUEST_URI'];

            return response(\File::get($filepath), 200, ['Content-type' => $this->getContentType($filepath)]);
        } catch (FileNotFoundException $e) {
            abort(404);
        }
    }

    private function resolveEntity($site_hash, $site_type): AbstractEntity
    {
        if ($site_type === 'prelanding') {
            return (new Transit())->getByHash($site_hash, ['system_domain']);
        }

        return (new Landing())->getByHash($site_hash, ['system_domain']);
    }

    private function getSymlink($site_type, $site_hash): string
    {
        return (string)config('env.domains_path')
            . $this->landing_handler->getBaseTagPath($site_type, $site_hash);
    }
}
