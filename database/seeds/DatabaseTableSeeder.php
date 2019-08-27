<?php

use Illuminate\Database\Seeder;
use App\Models\Database;

class DatabaseTableSeeder extends Seeder
{
    public function run()
    {
        if (Database::all()->count()) {
            return;
        }

        $databases = [[
            'name' => 'Default',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => Crypt::encrypt(env('DB_PASSWORD')),
            'port' => env('DB_PORT'),
            'type' => 'visitor'
        ]];

        foreach ($databases AS $database) {
            Database::create($database);
        }
    }
}
