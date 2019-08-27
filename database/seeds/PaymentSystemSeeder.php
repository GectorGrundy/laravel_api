<?php
declare(strict_types=1);

use App\Models\Currency;
use Illuminate\Database\Seeder;
use App\Models\PaymentSystem;

class PaymentSystemSeeder extends Seeder
{
    public function run()
    {
        if (PaymentSystem::all()->count()) {
            return;
        }

        // Webmoney
        PaymentSystem::create([
            'title' => 'Webmoney',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::RUB_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Webmoney',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::USD_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Webmoney',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::EUR_ID,
        ]);


        // Paxum
        PaymentSystem::create([
            'title' => 'Paxum',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::RUB_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Paxum',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::USD_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Paxum',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::EUR_ID,
        ]);


        // Epayments
        PaymentSystem::create([
            'title' => 'Epayments',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::RUB_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Epayments',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::USD_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Epayments',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::EUR_ID,
        ]);


        // Cash
        PaymentSystem::create([
            'title' => 'Cash',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::RUB_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Cash',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::USD_ID,
        ]);
        PaymentSystem::create([
            'title' => 'Cash',
            'status' => 'active',
            'min_payout' => 500,
            'fixed_comission' => 5,
            'percentage_comission' => 1,
            'currency_id' => Currency::EUR_ID,
        ]);

        // Swift
//        PaymentSystem::create([
//            'title' => 'Swift',
//            'status' => 'active',
//            'min_payout' => 500,
//            'fixed_comission' => 5,
//            'percentage_comission' => 1,
//            'currency_id' => Currency::RUB_ID,
//        ]);
//        PaymentSystem::create([
//            'title' => 'Swift',
//            'status' => 'active',
//            'min_payout' => 500,
//            'fixed_comission' => 5,
//            'percentage_comission' => 1,
//            'currency_id' => Currency::USD_ID,
//        ]);
//        PaymentSystem::create([
//            'title' => 'Swift',
//            'status' => 'active',
//            'min_payout' => 500,
//            'fixed_comission' => 5,
//            'percentage_comission' => 1,
//            'currency_id' => Currency::EUR_ID,
//        ]);
    }
}
