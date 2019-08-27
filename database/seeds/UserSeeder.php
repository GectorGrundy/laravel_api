<?php
declare(strict_types=1);

use App\Models\AdministratorProfile;
use App\Models\Advertiser;
use App\Models\Currency;
use App\Models\ManagerProfile;
use App\Models\SupportProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private $test_manager_id;
    
    public function run()
    {
        $this->test_manager_id = config('env.test_manager_id');
        
        if (User::all()->count()) {
            return;
        }

        $this->createTestUsers();
        $this->createTestAdvertisers();
        $this->createRandomUsers();
        $this->setPermissions();
    }

    private function createTestUsers()
    {
        # Admin
        $administrator = factory(App\Models\Administrator::class)
            ->create([
                'email' => config('env.test_admin_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_admin_email'))[0]
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
                'group_id' => \App\Models\UserGroup::DEFAULT_ID,
            ]);
        $fallback_advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($fallback_advertiser);

        # Test publisher
        $test_publisher = factory(App\Models\Publisher::class)
            ->create([
                'email' => config('env.test_publisher_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_publisher_email'))[0],
                'group_id' => \App\Models\UserGroup::DEFAULT_ID,
            ]);
        $test_publisher->profile()
            ->save(factory(App\Models\PublisherProfile::class)->make(['support_id' => 6]));

        # Test advertiser
        $test_advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => config('env.test_advertiser_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_advertiser_email'))[0]
            ]);
        $test_advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($test_advertiser);

        # Test support
        $support = factory(App\Models\Support::class)
            ->create([
                'email' => config('env.test_support_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_support_email'))[0]
            ]);
        $support->profile()->save(factory(SupportProfile::class)->make());

        # Test manager
        $manager = factory(App\Models\Manager::class)
            ->create([
                'email' => config('env.test_manager_email'),
                'password' => 'secret',
                'nickname' => explode('@', config('env.test_manager_email'))[0]
            ]);
        $manager->profile()->save(factory(ManagerProfile::class)->make());
    }

    private function createTestAdvertisers()
    {
        # Approveninja
        /**
         * @var \App\Models\Advertiser $advertiser
         */
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'approveninja@affninja.com',
                'password' => 'secret',
                'nickname' => 'approveninja'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Terraleads
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'terraleads@affninja.com',
                'password' => 'secret',
                'nickname' => 'terraleads'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Leadbit
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'leadbit@affninja.com',
                'password' => 'secret',
                'nickname' => 'leadbit'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Kma
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'kma@affninja.com',
                'password' => 'secret',
                'nickname' => 'kma'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # cpawebnet
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'cpawebnet@affninja.com',
                'password' => 'secret',
                'nickname' => 'cpawebnet'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Adcombo
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'adcombo@affninja.com',
                'password' => 'secret',
                'nickname' => 'adcombo'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Affbay
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'affbay@affninja.com',
                'password' => 'secret',
                'nickname' => 'affbay'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # Monsterleads
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'monsterleads@affninja.com',
                'password' => 'secret',
                'nickname' => 'monsterleads'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);

        # LoremIpsuma
        $advertiser = factory(App\Models\Advertiser::class)
            ->create([
                'email' => 'loremipsuma@affninja.com',
                'password' => 'secret',
                'nickname' => 'loremipsuma'
            ]);
        $advertiser->profile()->save(factory(App\Models\AdvertiserProfile::class)->make([
            'manager_id' => $this->test_manager_id
        ]));
        $this->createAccounts($advertiser);
    }

    private function createRandomUsers()
    {
        factory(App\Models\Administrator::class, 10)
            ->create()
            ->each(function (User $user) {
                $user->profile()->save(factory(AdministratorProfile::class)->make());
            });

        factory(App\Models\Support::class, 3)
            ->create()
            ->each(function (User $support) {
                $support->profile()->save(factory(\App\Models\SupportProfile::class)->make());

                factory(App\Models\Publisher::class, 20)
                    ->create([
                        'group_id' => \App\Models\UserGroup::DEFAULT_ID,
                    ])
                    ->each(function (User $user) use ($support) {
                        $user->profile()->save(factory(\App\Models\PublisherProfile::class)->make([
                            'support_id' => $support['id']
                        ]));
                    });
            });
    }

    private function setPermissions()
    {
        DB::table('user_user_permission')->insert([[
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'user_permission_id' => 1,
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
        ], [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'user_permission_id' => 2,
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
        ], [
            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
            'user_permission_id' => 3,
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
        ]]);
    }

    private function createAccounts(Advertiser $advertiser)
    {
        $advertiser->accounts()->create([
            'user_id' => $advertiser['user_id'],
            'currency_id' => Currency::RUB_ID,
        ]);
        $advertiser->accounts()->create([
            'user_id' => $advertiser['user_id'],
            'currency_id' => Currency::USD_ID,
        ]);
        $advertiser->accounts()->create([
            'user_id' => $advertiser['user_id'],
            'currency_id' => Currency::EUR_ID,
        ]);
    }
}
