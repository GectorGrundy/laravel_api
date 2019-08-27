<?php

use App\Models\Currency;
use App\Models\TargetGeo;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */

$factory->define(TargetGeo::class, function (Faker\Generator $faker) {

    return [
        'offer_id' => 1,
        'target_id' => 1,
        'country_id' => 1,
        'payout_currency_id' => Currency::USD_ID,
        'price_currency_id' => Currency::USD_ID,
        'payout' => 500,
        'profit' => 50,
        'hold_time' => 1440,
        'price' => 1000,
        'old_price' => 2000,
        'is_default' => 1,
        'is_active' => 1,
        'target_geo_rule_sort_type' => TargetGeo::RULE_PRIOITY_SORT,
    ];
});