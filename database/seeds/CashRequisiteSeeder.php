<?php

use App\Models\CashRequisite;
use App\Models\PaymentSystem;
use Illuminate\Database\Seeder;

class CashRequisiteSeeder extends Seeder
{
    public function run()
    {
        CashRequisite::create([
            'payment_system_id' => PaymentSystem::CASH_RUB,
            'title' => 'Cash',
        ]);
        CashRequisite::create([
            'payment_system_id' => PaymentSystem::CASH_USD,
            'title' => 'Cash',
        ]);
        CashRequisite::create([
            'payment_system_id' => PaymentSystem::CASH_EUR,
            'title' => 'Cash',
        ]);
    }
}
