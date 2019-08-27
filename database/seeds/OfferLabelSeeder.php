<?php

use Illuminate\Database\Seeder;

use App\Models\OfferLabel;

class OfferLabelSeeder extends Seeder
{
    public function run()
    {
        if (OfferLabel::all()->count()) {
            return;
        }

        $labels = [[
            'title' => 'Hot',
            'color' => '#b72427'
        ], [
            'title' => 'New',
            'color' => '#2ec130'
        ]];

        foreach ($labels as $label) {
            OfferLabel::create($label);
        }
    }
}
