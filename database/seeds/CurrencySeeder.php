<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        if (Currency::all()->count()) {
            return;
        }

        $currencies = json_decode(File::get(storage_path('files/currencies.json')), true);

        foreach ($currencies AS $currency) {

            Currency::create([
                'title' => $currency['title'],
                'code' => $currency['code'],
                'sign' => $currency['sign']
            ]);
        }
    }
}
