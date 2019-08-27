<?php
declare(strict_types=1);

namespace App\Factories;

use App\Integrations\FraudFilter\FraudFilterCloakVerification;
use App\Integrations\FraudFilter\TestCloakVerification;
use App\Integrations\Keitaro\KeitaroCloakVerification;

class CloakSystemFactory
{
    public static function getInstance(string $cloak_system_title, array $config)
    {
        switch (strtolower($cloak_system_title)) {
            case 'fraudfilter':
                return new FraudFilterCloakVerification(
                    $config['api_key'],
                    $config['campaign_id'],
                    $config['customer_email'] ?? ''
                );

            case 'keitaro':
                return new KeitaroCloakVerification($config['api_key'], $config['alias']);

            case 'test':
                return new TestCloakVerification();

            default:
                throw new \BadMethodCallException();
        }
    }
}
