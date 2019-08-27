<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Currency;

class CurrencyRatesCacher extends Command
{
    protected $signature = 'currency:cache_rates';
    protected $description = 'Refresh rates of currencies cache';

    public function handle()
    {
	    (new Currency())->cacheRates();
    }
}
