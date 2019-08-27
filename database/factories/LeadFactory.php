<?php

use App\Models\Lead;
use Carbon\Carbon;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */
$factory->define(Lead::class, function (Faker\Generator $faker) {

    $created_at = $updated_at = $faker->dateTimeBetween('-7 days', 'now')->format('Y-m-d H:i:s');
    $initialized_at = Carbon::createFromFormat('Y-m-d H:i:s', $created_at)->subMinutes(5)->toDateTimeString();

    return [
        'integration_id' => 10,
        'status' => Lead::NEW,
        'is_valid_phone' => 1,
        'offer_id' => 1,
        'target_id' => 1,
        'locale_id' => 1,
        'target_geo_rule_id' => 1,
        'target_geo_id' => 1,
        'country_id' => 1,
        'region_id' => 1,
        'city_id' => 1,
        'currency_id' => 1,
        'publisher_id' => env('TEST_PUBLISHER_ID', 1),
        'advertiser_id' => env('TEST_ADVERTISER_ID', 0),
        'landing_id' => 1,
        'transit_id' => 1,
        'flow_id' => 2,
        'order_id' => function () {
            return factory(\App\Models\Order::class)->create()->id;
        },
        'payout' => 500,
        'profit' => 50,
        'price' => 1000,
        'origin' => Lead::WEB_ORIGIN,
        'type' => Lead::COD_TYPE,
        'ip_country_id' => 1,
        'browser_id' => 1,
        'os_platform_id' => 1,
        'device_type_id' => 1,
        'transit_traffic_type' => 'click',
        'browser_locale' => 'ru',
        'ip' => $faker->ipv4,
        'ips' => '{}',
        'data1' => 'data1',
        'data2' => 'data2',
        'data3' => 'data3',
        'data4' => 'data4',
        'clickid' => str_random(32),
        's_id' => str_random(32),
        'user_agent' => $faker->userAgent,
        'referer' => $faker->url,
        'hold_time' => 1440,
        'initialized_at' => $initialized_at,
        'created_at' => $created_at,
        'updated_at' => $updated_at,
    ];
});

$factory->defineAs(Lead::class, 'usd', function (Faker\Generator $faker) use ($factory) {
    $lead = $factory->raw(Lead::class);
    return array_merge($lead, ['currency_id' => 3]);
});

$factory->defineAs(Lead::class, 'eur', function (Faker\Generator $faker) use ($factory) {
    $lead = $factory->raw(Lead::class);
    return array_merge($lead, ['currency_id' => 5]);
});

$factory->defineAs(Lead::class, 'approved', function (Faker\Generator $faker) use ($factory) {
    $lead = $factory->raw(Lead::class);
    return array_merge($lead, ['status' => 'approved']);
});

$factory->defineAs(Lead::class, 'cancelled', function (Faker\Generator $faker) use ($factory) {
    $lead = $factory->raw(Lead::class);
    return array_merge($lead, ['status' => 'cancelled']);
});

$factory->defineAs(Lead::class, 'trashed', function (Faker\Generator $faker) use ($factory) {
    $lead = $factory->raw(Lead::class);
    return array_merge($lead, ['status' => 'trashed']);
});