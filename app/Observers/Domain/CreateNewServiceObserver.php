<?php
declare(strict_types=1);

namespace App\Observers\Domain;

use App\Models\Domain;

/**
 * Управление системными доменами лендингов/прелендингов при добавлении/изменении сервисных доменов
 *
 * @todo Дописать реализацию
 */
class CreateNewServiceObserver
{
    public function updated(Domain $domain): void
    {
        if (!$domain->isService()) {
            return;
        }

        // Если новый - перебрать все сущности и добавить для них текущих домен
    }

    public function deleting(Domain $domain): void
    {
        if (!$domain->isService()) {
            return;
        }

        // Удалить все системные домены сервисного домена
    }
}
