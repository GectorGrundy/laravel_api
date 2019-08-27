<?php

use App\Models\CloakSystem;
use Illuminate\Database\Seeder;

class CloakSystemSeeder extends Seeder
{
    public function run()
    {
        if (CloakSystem::all()->count()) {
            return;
        }
        CloakSystem::create([
            'title' => 'Test',
            'schema' => json_encode([]),
        ]);
        CloakSystem::create([
            'title' => 'FraudFilter',
            'schema' => json_encode([
                'api_key' => 'required|string',
                'campaign_id' => 'required|string',
                'customer_email' => 'required|string',
            ]),
        ]);
        CloakSystem::create([
            'title' => 'Keitaro',
            'schema' => json_encode([
                'api_key' => 'required|string',
                'alias' => 'required|url',
            ]),
        ]);
    }
}
