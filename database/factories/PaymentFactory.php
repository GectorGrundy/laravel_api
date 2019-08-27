<?php
declare(strict_types=1);

use App\Models\Payment;
use App\Models\User;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */
$factory->define(Payment::class, function (Faker\Generator $faker) {

    $created_at = $updated_at = $faker->dateTimeBetween('-7 days', 'now');
    $payout = $faker->numberBetween(500, 5000);

    return [
        'user_id' => SeederConstants::TEST_PUBLISHER_ID,
        'user_role' => User::PUBLISHER,
        'requisite_id' => 1,
        'requisite_type' => 'webmoney',
        'status' => Payment::PENDING,
        'type' => 'payment',
        'currency_id' => 1,
        'payout' => $faker->numberBetween(500, 5000),
        'balance_payout' => $payout,
        'description' => 'Ondemand payment',
        'created_at' => $created_at,
        'updated_at' => $created_at,
    ];
});

$factory->defineAs(Payment::class, Payment::ACCEPTED, function () use ($factory) {
    $payment = $factory->raw(Payment::class);
    return array_merge($payment, ['status' => Payment::ACCEPTED]);
});

$factory->defineAs(Payment::class, Payment::CANCELLED, function () use ($factory) {
    $payment = $factory->raw(Payment::class);
    return array_merge($payment, ['status' => Payment::CANCELLED]);
});

$factory->defineAs(Payment::class, Payment::PAID, function () use ($factory) {
    $payment = $factory->raw(Payment::class);
    return array_merge($payment, ['status' => Payment::PAID]);
});