<?php

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Seeder;

class UserPermissionsSeeder extends Seeder
{
    public function run()
    {
        UserPermission::create([
            'title' => 'API',
            'description' => 'API',
            'toggle_type' => 'including',
            'user_role' => User::PUBLISHER,
        ]);
        UserPermission::create([
            'title' => 'FLOW_CUSTOM_CODE',
            'description' => 'Кастомный код потока',
            'toggle_type' => 'including',
            'user_role' => User::PUBLISHER,
        ]);
        UserPermission::create([
            'title' => 'CLOAKING',
            'description' => 'Клоакинг',
            'toggle_type' => 'including',
            'user_role' => User::PUBLISHER,
        ]);
    }
}
