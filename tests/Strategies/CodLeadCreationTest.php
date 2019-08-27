<?php
declare(strict_types=1);

namespace Tests\Strategies;

use App\Models\Landing;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CodLeadCreationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function lead_creates()
    {
        $landing = Landing::with(['domains', 'target.target_geo'])->first();
        $_SERVER['HTTP_HOST'] = $landing->domains[0]->domain;

        $this->post($landing->domains[0]->host . '/order.html', [
            'client' => 'name',
            'phone' => '0900000000',
            'target_geo_hash' => $landing->target->target_geo[0]->hash,
        ]);

        $this->assertEquals(1, Lead::all()->count());
        $this->assertEquals(1, Order::all()->count());
    }

    /**
     * @dataProvider badOrderDataProvider
     */
    public function test_getting_validation_error_when_bad_data($name, $phone, $target_geo_hash)
    {
        $landing = Landing::with(['domains', 'target.target_geo'])->first();
        $_SERVER['HTTP_HOST'] = $landing->domains[0]->domain;

        $response = $this->post($landing->domains[0]->host . '/order.html', [
            'client' => $name,
            'phone' => $phone,
            'target_geo_hash' => $landing->target->target_geo[0]->hash,
        ]);

//        $response->assertStatus(422);
        $this->assertEquals(0, Lead::all()->count());
        $this->assertEquals(0, Order::all()->count());
    }

    public function badOrderDataProvider()
    {
        return [
            [
                'name' => '',
                'phone' => '',
                'target_geo_hash' => '',
            ]
        ];
    }
}