<?php
declare(strict_types=1);

namespace App\Contracts;

/**
 * Контракт, который должны реализововать классы интеграций с системами клоакинга
 */
interface CloakSystemDetector
{
    public function isSafePage(): bool;
}