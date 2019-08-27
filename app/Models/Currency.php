<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use Exception;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use App\Exceptions\Currency\CannotParseRateException;

class Currency extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    public const RUB_ID = 1;
    public const USD_ID = 3;
    public const EUR_ID = 5;

    public const PAYOUT_CURRENCIES = [self::RUB_ID, self::USD_ID, self::EUR_ID];
    public const PAYOUT_CURRENCIES_STR = self::RUB_ID . ',' . self::USD_ID . ',' . self::EUR_ID;
    public const CHARGE_CURRENCIES_STR = self::RUB_ID . ',' . self::USD_ID . ',' . self::EUR_ID;
    public const CONVERTABLE = [
        'USDEUR', 'USDRUB',
        'RUBUSD', 'RUBEUR',
        'EURUSD', 'EURRUB',
        'GBPUSD', 'GBPRUB', 'GBPEUR',
        'DKKEUR', 'DKKRUB', 'DKKUSD',
        'SEKEUR', 'SEKRUB', 'SEKUSD',
    ];

    protected $fillable = ['title', 'code', 'sign', 'sign_side', 'rates'];
    protected $hidden = ['rates', 'created_at', 'updated_at'];

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Получение инфо о валюте
     *
     * @param $currency_id
     * @return self
     */
    public function getInfo($currency_id): self
    {
        return self::find($currency_id);
    }

    /**
     * Конвертация суммы в нужную валюту
     *
     * @param int $currency_from_id
     * @param int $currency_to_id
     * @param float $amount
     * @return float
     */
    public function convert(int $currency_from_id, int $currency_to_id, float $amount): float
    {
        if ($currency_from_id === $currency_to_id || $amount === 0) {
            return $amount;
        }

        $currency_from = $this->getInfo($currency_from_id);
        $currency_to = $this->getInfo($currency_to_id);

        $rate = $this->getRate($currency_from['code'], $currency_to['code']);

        return round($rate * $amount, 5);
    }

    /**
     * Получаем курс конвертируемой валюты
     *
     * @param $currency_from_code
     * @param $currency_to_code
     * @param $no_cache
     *
     * @return float
     */
    private function getRate($currency_from_code, $currency_to_code, $no_cache = false): float
    {
        $key = "currency:getRate:{$currency_from_code}:{$currency_to_code}";

        if ($no_cache) {
            Cache::forget($key);
        }

        $rate = Cache::get($key, function () use ($key, $currency_from_code, $currency_to_code) {

            $currency = self::select('rates')->whereCode($currency_from_code)->first();

            $rate = json_decode($currency->rates, true)[$currency_to_code];

            Cache::forever($key, $rate);

            return $rate;
        });

        return (float)$rate;

    }

    /**
     * Получаем курс конвертируемой валюты с Yahoo API
     *
     * @return array
     */
    private function getRates()
    {
//       return $this->getYahooRates();
        return $this->getHerokuRates();
    }

    private function getYahooRates()
    {
        $url = 'https://developer.yahoo.com/yql/console/proxy.php';
        $params = [
            'q' => 'select * from yahoo.finance.xchange where pair in (' . '"' . implode('", "', self::CONVERTABLE) . '"' . ')',
            'env' => 'store://datatables.org/alltableswithkeys',
            'format' => 'json',
            'crumb' => 'umQwJCRjmVp',
        ];
        $response = sendHttpRequest('POST', $url, $params);

        return $this->parseYahooRates($response);
    }

    /**
     * Разбор ответа от Yahoo и формирование массива с рейтами
     *
     * @param $rates
     *
     * @return array
     *
     * @throws \App\Exceptions\Currency\CannotParseRateException
     */
    private function parseYahooRates($rates): array
    {
        try {
            $rates = json_decode($rates, true)['query']['results'];
            $result = [];
            foreach ($rates['rate'] as $rate) {

                list($currency_from, $currency_to) = explode('/', $rate['Name']);

                if (!isset($result[$currency_from])) {
                    $result[$currency_from] = [];
                }

                $result[$currency_from]['currencies'][$currency_to] = $rate['Rate'];
            }
        } catch (Exception $e) {
            throw new CannotParseRateException();
        }

        return $result;
    }

    private function getHerokuRates()
    {
        $result = [];
        $url = 'https://adsynth-ofx-quotewidget-prod.herokuapp.com/api/1';

        foreach (self::CONVERTABLE as $currencies) {
            sleep(1);
            $from = substr($currencies, 0, 3);
            $to = substr($currencies, 3, 3);

            $json = json_encode([
                'method' => 'spotRateHistory',
                'data' => [
                    'base' => $from,
                    'term' => $to,
                    'period' => 'day',
                ]
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json',]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($ch);
            curl_close($ch);

            $rate = json_decode($response, true)['data']['CurrentInterbankRate'];
            if (!isset($result[$from])) {
                $result[$from] = [];
            }

            $result[$from]['currencies'][$to] = number_format($rate, 4);
        }

        return $result;
    }

    /**
     * Кэширование курсов валют
     */
    public function cacheRates()
    {
        $currencies_rates = $this->getRates();

        foreach ($currencies_rates as $currency_from => $rate_date) {

            $currency_rates = [];
            foreach ($rate_date['currencies'] as $currency_to_code => $rate) {

                $currency_rates[$currency_to_code] = $rate;

                $key = "currency:getRate:{$currency_from}:{$currency_to_code}";

                Cache::forever($key, $rate);
            }

            self::whereCode($currency_from)->update([
                'rates' => json_encode($currency_rates)
            ]);
        }
    }

    public function getFormattedPrice($price)
    {
        $price = number_format((float)$price, 0, '.', ',');

        return $this->sign_side === 'left' ? $this->sign . $price : $price . $this->sign;
    }
}
