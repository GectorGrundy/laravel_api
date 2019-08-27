<?php
declare(strict_types=1);

use App\Models\Landing;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */
$factory->define(Landing::class, function (Faker\Generator $faker) {
    return [
        'title' => 'landing',
        'subdomain' => 'landing',
        'offer_id' => 1,
        'target_id' => 1,
        'locale_id' => 1,
        'is_active' => 1,
        'is_private' => 1,
        'is_responsive' => 1,
        'is_mobile' => 1,
        'is_advertiser_viewable' => 1,
        'is_address_on_success' => 1,
        'is_email_on_success' => 1,
        'is_custom_success' => 1,
        'type' => Landing::COD,
    ];
});