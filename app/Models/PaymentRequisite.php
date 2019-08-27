<?php
declare(strict_types=1);

namespace App\Models;

use Hashids;
use Illuminate\Database\Eloquent\Model;

class PaymentRequisite
{
    public static function getByHash(PaymentSystem $payment_system, string $hash): Model
    {
        $id = Hashids::decode($hash)[0];

        return self::getById($payment_system, $id);
    }

    public static function getById(PaymentSystem $payment_system, int $id): Model
    {
        $model = self::getModelInstanse($payment_system);

        return $model->find($id);
    }

    private static function getModelInstanse(PaymentSystem $payment_system): Model
    {
        switch ($payment_system->id) {
            case PaymentSystem::WEBMONEY_RUB:
            case PaymentSystem::WEBMONEY_USD:
            case PaymentSystem::WEBMONEY_EUR:
                return new WebmoneyRequisite();

            case PaymentSystem::PAXUM_RUB:
            case PaymentSystem::PAXUM_USD:
            case PaymentSystem::PAXUM_EUR:
                return new PaxumRequisite();

            case PaymentSystem::EPAYMENTS_RUB:
            case PaymentSystem::EPAYMENTS_USD:
            case PaymentSystem::EPAYMENTS_EUR:
                return new EpaymentsRequisite();

            case PaymentSystem::CASH_RUB:
            case PaymentSystem::CASH_USD:
            case PaymentSystem::CASH_EUR:
                return new CashRequisite();

            case PaymentSystem::SWIFT_RUB:
            case PaymentSystem::SWIFT_USD:
            case PaymentSystem::SWIFT_EUR:
                return new SwiftRequisite();
        }
    }
}
