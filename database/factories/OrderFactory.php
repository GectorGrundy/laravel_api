<?php

use App\Models\Order;
use Carbon\Carbon;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */

$factory->define(Order::class, function (Faker\Generator $faker) {

    $phone = $faker->phoneNumber;
    $name = $faker->firstName;

    return [
        'name' => $faker->firstName . ' ' . $faker->lastName,
        'phone' => $faker->phoneNumber,
        'info' => json_encode([]),
        'email' => $faker->email,
        'products' => '{}',
        'is_corrected' => 0,
        'history' => json_encode([
            'date' => time(),
            'name' => $name,
            'origin_phone' => $phone,
            'phone' => $phone
        ]),
        'number_type_id' => Order::PHONE_MOBILE,
    ];
});

$factory->defineAs(Order::class, 'online', function (Faker\Generator $faker) use ($factory) {
    $order = $factory->raw(Order::class);

    $city = $faker->city;
    $country_id = 1;
    $zipcode = $faker->postcode;
    $street = $faker->streetAddress;
    $apartment = random_int(1, 100);
    $house = random_int(1, 30);

    $product_cost = random_int(100, 500);
    $product_cost_sign = 'руб';

    $delivery_cost = random_int(50, 200);
    $delivery_cost_sign = 'руб';

    $total_cost = $product_cost + $delivery_cost;
    $total_cost_sign = $product_cost + $delivery_cost;

    list($name, $last_name) = explode(' ', $order['name'])[1];

    $history = json_encode([
        'date' => time(),
        'name' => $name,
        'origin_phone' => $order['phone'],
        'phone' => $order['phone'],
    ]);


    return array_merge($order, [
        'integration_id' => 1,
        'info' => json_encode([
            'email' => $order['email'],
            'name' => $name,
            'last_name' => $last_name,
            'country_id' => $country_id,
            'product_cost' => $product_cost,
            'product_cost_sign' => $product_cost_sign,
            'delivery_cost' => $delivery_cost,
            'delivery_cost_sign' => $delivery_cost_sign,
            'total_cost' => $total_cost,
            'total_cost_sign' => $total_cost_sign,
            'street' => $street,
            'house' => $house,
            'apartment' => $apartment,
            'zipcode' => $zipcode,
            'city' => $city,
        ]),
        'email' => $order['email'],
        'history' => $history,
        'country_id' => $country_id,
        'product_cost' => $product_cost,
        'product_cost_sign' => $product_cost_sign,
        'delivery_cost' => $delivery_cost,
        'delivery_cost_sign' => $delivery_cost_sign,
        'total_cost' => $total_cost,
        'total_cost_sign' => $total_cost_sign,
        'last_name' => $last_name,
        'street' => $street,
        'house' => $house,
        'apartment' => $apartment,
        'zipcode' => $zipcode,
        'city' => $city,
    ]);
});

$factory->defineAs(Order::class, 'online_integrated', function (Faker\Generator $faker) use ($factory) {
    $order = $factory->raw(Order::class);
    $integrated_at = Carbon::now()->addHour();

    return array_merge($order, [
        'integrated_at' => $integrated_at,
    ]);
});

$factory->defineAs(Order::class, 'tracked', function (Faker\Generator $faker) use ($factory) {
    $order = $factory->rawOf(Order::class, 'online_integrated');
    $tracked_at = Carbon::createFromFormat('Y-m-d H:i:s', $order['integrated_at'])->addHour(3);

    return array_merge($order, [
        'tracked_at' => $tracked_at,
        'tracking_number' => str_random(16)
    ]);
});
