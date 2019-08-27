<?php
declare(strict_types=1);

namespace Tests\Strategies;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Traits\ApiRequest;

class AdvertiserChangeProfileTest extends TestCase
{
    use DatabaseTransactions;
    use ApiRequest;

    /** @test */
    public function changes_if_data_is_correct_for_admin()
    {
        [$token, $user] = $this->auth(User::ADVERTISER);

        $response = $this->post('advertiser.changeProfile', [
            'full_name' => 'fio',
            'skype' => 'skype',
            'telegram' => 'telegram',
            'phone' => '+380900000000',
            'whatsapp' => 'whatsapp',
        ], $this->headers($token));

        $response->assertStatus(200);
        $this->assertEquals('fio', $user->advertiser['full_name']);
        $this->assertEquals('skype', $user->advertiser['skype']);
        $this->assertEquals('telegram', $user->advertiser['telegram']);
        $this->assertEquals('+380900000000', $user->advertiser['phone']);
        $this->assertEquals('whatsapp', $user->advertiser['whatsapp']);
    }
}