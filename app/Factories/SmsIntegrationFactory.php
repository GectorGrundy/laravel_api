<?php
declare(strict_types=1);

namespace App\Factories;

use App\Integrations\Turbosms\TurbosmsSendOrderTrackNumberSms;

class SmsIntegrationFactory
{
    public static function getInstance(string $integration_title, int $order_id, int $sms_integration_id)
    {
        switch (strtolower($integration_title)) {
            case 'turbosms':
                return new TurbosmsSendOrderTrackNumberSms($order_id, $sms_integration_id);

            default:
                throw new \BadMethodCallException();
        }
    }
}
