<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;
use App\Models\Integration;

class IntegrationSeeder extends Seeder
{
    public function run()
    {
        if (Integration::all()->count()) {
            return;
        }

        $integrations = include __DIR__ . '/stabs/integrations.php';

        // Записи добавляются через модель потому что при создании интеграции генерируется internal_api_key
        foreach ($integrations AS $integration) {
            Integration::create($integration);
        }
    }
}
