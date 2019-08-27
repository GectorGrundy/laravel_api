<?php
declare(strict_types=1);

use App\Models\Domain;
use App\Models\EpaymentsRequisite;
use App\Models\PaxumRequisite;
use App\Models\PaymentSystem;
use App\Models\SwiftRequisite;
use App\Models\Target;
use App\Models\WebmoneyRequisite;
use Illuminate\Database\Seeder;
use App\Models\Landing;

class PaymentRequsitesSeeder extends Seeder
{
    public function run()
    {
        $webmoney = [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
        ];
        WebmoneyRequisite::create(array_merge($webmoney, [
            'payment_system_id' => PaymentSystem::WEBMONEY_RUB,
            'purse' => 'R123456789012'
        ]));
        WebmoneyRequisite::create(array_merge($webmoney, [
            'payment_system_id' => PaymentSystem::WEBMONEY_USD,
            'purse' => 'Z123456789012'
        ]));
        WebmoneyRequisite::create(array_merge($webmoney, [
            'payment_system_id' => PaymentSystem::WEBMONEY_EUR,
            'purse' => 'E123456789012'
        ]));

        $paxum = [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'email' => 'test@test.test'
        ];
        PaxumRequisite::create(array_merge($paxum, [
            'payment_system_id' => PaymentSystem::PAXUM_RUB,
        ]));
        PaxumRequisite::create(array_merge($paxum, [
            'payment_system_id' => PaymentSystem::PAXUM_USD,
        ]));
        PaxumRequisite::create(array_merge($paxum, [
            'payment_system_id' => PaymentSystem::PAXUM_EUR,
        ]));

        $epayments = [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'ewallet' => '000-123456'
        ];
        EpaymentsRequisite::create(array_merge($epayments, [
            'payment_system_id' => PaymentSystem::EPAYMENTS_RUB,
        ]));
        EpaymentsRequisite::create(array_merge($epayments, [
            'payment_system_id' => PaymentSystem::EPAYMENTS_USD,
        ]));
        EpaymentsRequisite::create(array_merge($epayments, [
            'payment_system_id' => PaymentSystem::EPAYMENTS_EUR,
        ]));

        $swift = [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'card_number' => '1111222233334444',
            'card_holder' => 'Test Test',
            'expires' => '01/17',
            'birthday' => '1990-03-34',
            'document' => 'KE909090',
            'country' => 'Ukraine',
            'street' => 'Street',
            'phone' => '0900000000',
            'tax_id' => 777,
        ];
        SwiftRequisite::create(array_merge($swift, [
            'payment_system_id' => PaymentSystem::SWIFT_RUB,
        ]));
        SwiftRequisite::create(array_merge($swift, [
            'payment_system_id' => PaymentSystem::SWIFT_USD,
        ]));
        SwiftRequisite::create(array_merge($swift, [
            'payment_system_id' => PaymentSystem::SWIFT_EUR,
        ]));
    }
}
