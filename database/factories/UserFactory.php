<?php
declare(strict_types=1);

use App\Models\{
    Manager, ManagerProfile, User, Publisher, PublisherProfile, Administrator, AdministratorProfile, Advertiser,
    AdvertiserProfile, Support, SupportProfile, UserGroup
};

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

// User
$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'password' => 'secret',
        'status' => User::ACTIVE,
        'group_id' => UserGroup::DEFAULT_ID,
        'remember_token' => str_random(10),
    ];
});

// Administrator
$factory->define(Administrator::class, function ($faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['role' => User::ADMINISTRATOR]);
});
$factory->define(AdministratorProfile::class, function (Faker\Generator $faker) {
    return [
        'full_name' => $faker->name,
        'skype' => $faker->word,
        'telegram' => $faker->word,
    ];
});

// Publisher
$factory->define(Publisher::class, function ($faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['role' => User::PUBLISHER]);
});
$factory->define(PublisherProfile::class, function (Faker\Generator $faker) {
    return [
        'full_name' => $faker->name,
        'skype' => $faker->word,
        'tl' => $faker->numberBetween($min = 0, $max = 3)
    ];
});

// Advertiser
$factory->define(Advertiser::class, function ($faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['role' => User::ADVERTISER]);
});
$factory->define(AdvertiserProfile::class, function (Faker\Generator $faker) {
    return [
        'full_name' => $faker->name,
        'skype' => $faker->word,
        'whatsapp' => $faker->word,
    ];
});

// Support
$factory->define(Support::class, function ($faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['role' => User::SUPPORT]);
});
$factory->define(SupportProfile::class, function (Faker\Generator $faker) {
    return [
        'full_name' => $faker->name,
        'skype' => $faker->word,
        'telegram' => $faker->word,
    ];
});

// Manager
$factory->define(Manager::class, function ($faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['role' => User::MANAGER]);
});
$factory->define(ManagerProfile::class, function (Faker\Generator $faker) {
    return [
        'full_name' => $faker->name,
        'skype' => $faker->word,
        'telegram' => $faker->word,
    ];
});
