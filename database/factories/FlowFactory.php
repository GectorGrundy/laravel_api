<?php

use App\Models\Flow;
use App\Models\Offer;

/**
 * @var $factory Illuminate\Database\Eloquent\Factory
 */
$factory->define(Flow::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'status' => Flow::ACTIVE,
        'extra_flow_id' => 0,
        'publisher_id' => SeederConstants::TEST_PUBLISHER_ID,
        'is_detect_bot' => 1,
        'is_cpc' => 1,
        'cpc' => 3,
        'cpc_currency_id' => 1,
        'cpc_lost' => 5,
        'is_hide_target_list' => 1,
        'is_noback' => 1,
        'is_comebacker' => 1,
        'is_show_requisite' => 1,
        'is_remember_landing' => 0,
        'is_remember_transit' => 0,
        'tb_url' => '',
    ];
});