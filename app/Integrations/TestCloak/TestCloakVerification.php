<?php
declare(strict_types=1);

namespace App\Integrations\FraudFilter;

/**
 * Заглушка для тестирования клоакинга
 */
class TestCloakVerification
{
    public function handle(): bool
    {
        return false;
    }
}
