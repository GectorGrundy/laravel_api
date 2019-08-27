<?php
declare(strict_types=1);

use App\Models\DeviceType;
use Illuminate\Database\Seeder;
use App\Models\Browser;
use App\Models\OsPlatform;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        $this->seedDeviceTypes();
//        $this->seedBrowsers();
//        $this->seedOsPlatforms();
    }

    private function seedDeviceTypes()
    {
        if (DeviceType::all()->count()) {
            return;
        }

        $device_types = ['Desktop', 'Mobile phone', 'Tablet'];

        foreach ($device_types AS $device_type) {
            DeviceType::create([
                'title' => $device_type
            ]);
        }

    }

    private function seedBrowsers()
    {
        if (Browser::all()->count()) {
            return;
        }

        $browsers = ['Chrome 17', 'Chrome 18', 'Chrome 19', 'Firefox 46', 'Firefox 47', 'Safari 9', 'Safari 10'];
        foreach ($browsers as $browser) {
            Browser::create([
                'title' => $browser,
            ]);
        }
    }

    private function seedOsPlatforms()
    {
        if (OsPlatform::all()->count()) {
            return;
        }

        OsPlatform::updateOrCreate([
            'title' => 'Windows 7'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'Windows 10'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'Linux'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'IOS 7'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'IOS 8'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'IOS 9'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'IOS 10'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'Android 4.0.4'
        ]);
        OsPlatform::updateOrCreate([
            'title' => 'Android 6'
        ]);
    }
}
