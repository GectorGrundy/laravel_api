<?php
declare(strict_types=1);

$factory->define(App\Models\News::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence($nbWords = 8),
        'body' => $faker->text($maxNbChars = 500),
        'author_id' => 1,
    ];
});