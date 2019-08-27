<?php
declare(strict_types=1);

use App\Models\CloakDomainPath;
use App\Models\CloakDomainPathCloakSystem;
use App\Models\ManagerProfile;
use App\Models\OfferCategory;
use App\Models\SupportProfile;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Seeder;
use App\Models\Domain;

class ProductionSeeder extends Seeder
{
    public function run()
    {
        $this->call(CountrySeeder::class);
        $this->call(CountryTranslationSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(DeviceSeeder::class);
        $this->seedDomains();
        $this->call(FlowWidgetSeeder::class);
        $this->call(LocaleSeeder::class);
        $this->seedOfferCategories();
        $this->call(OfferLabelSeeder::class);
        $this->call(OfferSourceSeeder::class);
        $this->call(OfferSourceTranslationSeeder::class);
        $this->call(PaymentSystemSeeder::class);
        $this->call(UserGroupSeeder::class);
        $this->call(TargetTemplateSeeder::class);
        $this->seedUserPermissions();
        $this->seedUsers();
    }

    private function seedDomains()
    {
        Domain::create([
            'domain' => 'firstclick.pro',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::TDS_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'aff1core.com',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::REDIRECT_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'blogpost.pro',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::TRANSIT_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'buylike.pro',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::LANDING_ENTITY_TYPE,
        ]);
    }

    private function seedOfferCategories()
    {
        OfferCategory::create([
            'title' => 'test'
        ]);
    }

    private function seedUserPermissions()
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
    }

    private function seedUsers()
    {
        # Admin
        $administrator = factory(App\Models\Administrator::class)
            ->create([
                'email' => config('env.test_admin_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_admin_email'))[0],
                'group_id' => 0,
            ]);
        $administrator->profile()->save(factory(App\Models\AdministratorProfile::class)->make());

        # Fallback publisher
        $fallback_publisher = factory(App\Models\Publisher::class)
            ->create([
                'email' => config('env.fallback_publisher_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.fallback_publisher_email'))[0],
                'group_id' => \App\Models\UserGroup::DEFAULT_ID,
            ]);
        $fallback_publisher->profile()
            ->save(factory(App\Models\PublisherProfile::class)->make(['support_id' => 6]));

        # Fallback advertiser
        $fallback_advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => config('env.fallback_advertiser_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.fallback_advertiser_email'))[0],
                'group_id' => 0,
            ]);
        $fallback_advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make());
    }
}
