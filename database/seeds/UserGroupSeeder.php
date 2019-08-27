<?php
declare(strict_types=1);

use App\Models\UserGroup;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    public function run()
    {
        UserGroup::create([
            'title' => 'TL0',
            'color' => '9bf2b0',
            'is_default' => 1,
        ]);
    }
}
