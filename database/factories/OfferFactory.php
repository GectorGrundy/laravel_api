<?php

use App\Models\Offer;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */
$factory->define(Offer::class, function (Faker\Generator $faker) {
    return [
        'title' => '',
        'url' => 'http://example.com',
        'status' => Offer::ACTIVE,
        'agreement' => $faker->sentence,
        'description' => $faker->sentence,
        'is_private' => 0,
    ];
});