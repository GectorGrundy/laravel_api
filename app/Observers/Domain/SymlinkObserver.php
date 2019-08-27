<?php
declare(strict_types=1);

namespace App\Observers\Domain;

use App\Models\Domain;
use App\Services\SymlinkManager;
use App\Exceptions\Domain\IncorrectEntityType;

/**
 * Генерация симлинка для transit и landing домена
 *
 * Метод created не нужен, так как для домена генерируется hash
 */
class SymlinkObserver
{
    private $symlink_manager;

    public function __construct(SymlinkManager $symlink_generator)
    {
        $this->symlink_manager = $symlink_generator;
    }

    public function updated(Domain $domain)
    {
        if (!$domain->hasSymlink()) {
            return;
        }

        if ($domain->getOriginal('entity_id') !== $domain->entity_id
            || $domain->getOriginal('domain') !== $domain->domain
        ) {
            $old_symlink = Domain::getSymlink($domain, true);
            $this->symlink_manager->delete($old_symlink);
        }

        $symlink = Domain::getSymlink($domain);
        $this->symlink_manager->create($domain->realpath, $symlink);
    }

    public function deleting(Domain $domain)
    {
        if (!$domain->hasSymlink()) {
            return;
        }

        $old_symlink = Domain::getSymlink($domain, true);
        $this->symlink_manager->delete($old_symlink);
    }
}
