<?php
declare(strict_types=1);

namespace Tests\Models;

use App\Models\Integration;
use App\Models\TargetGeoRule;
use Event;
use Tests\TestCase;
use App\Listeners\CreateLeadStatusLog;
use App\Listeners\PublisherStatisticUpdateApprovedLeads;
use App\Listeners\PublisherStatisticUpdatePayout;
use App\Listeners\SendPublisherPostback;
use App\Listeners\OnLeadApproved;
use App\Models\Lead;

class LeadTest extends TestCase
{
//    use DatabaseTransactions;

    /**
     * @test
     */
    public function integrates()
    {
        /**
         * @var Lead $lead
         */
        $lead = factory(Lead::class)->create();
        $integration = Integration::where('title', 'Test')->first();
        $target_geo_rule = TargetGeoRule::where('target_geo_id', $lead['target_geo_id'])->first();

        $lead = $lead->integrateByTargetGeoRule($integration, $target_geo_rule);

        $this->assertEquals($integration['id'], $lead['integration_id']);
        $this->assertEquals($target_geo_rule['id'], $lead['target_geo_rule_id']);
        $this->assertEquals(Lead::INTEGRATION, $lead['integration_type']);
        $this->assertEquals($target_geo_rule['advertiser_id'], $lead['advertiser_id']);

        return $lead;
    }

    /**
     * @test
     * @depends integrates
     */
    public function set_as_integrated(Lead $lead)
    {
        $lead->setAsIntegrated('test_external_key');

        $this->assertEquals('test_external_key', $lead['external_key']);

        return $this;
    }

    /**
     * @depends set_as_integrated
     */
    public function approves(Lead $lead)
    {
//        $this->assertTrue(true);


//        Event::fake();

        $lead = $lead->approve(Lead::PAID_SUBSTATUS_ID, 'Оплачен');

        $this->assertEquals(Lead::APPROVED, $lead['status'], 'Status set');
        $this->assertEquals(Lead::PAID_SUBSTATUS_ID, $lead['sub_status_id'], 'Substatus id set');
        $this->assertEquals('Оплачен', $lead['sub_status'], 'Substatus set');
        $this->assertNotNull($lead->processed_at, 'Установлена дата обработки');
        $this->assertEquals(1, $lead['is_hold'], 'Установлен флаг холда');
        $this->assertEquals($lead->target_geo['hold_time'], $lead['hold_time'], 'Установлено время холда');

        Event::assertDispatched(OnLeadApproved::class);
        Event::assertDispatched(CreateLeadStatusLog::class);
        Event::assertDispatched(SendPublisherPostback::class);
        Event::assertDispatched(PublisherStatisticUpdateApprovedLeads::class);
        Event::assertDispatched(PublisherStatisticUpdatePayout::class);

        return $lead;
    }

    /** @test */
//    public function cancelles()
//    {
//        /**
//         * @var Lead $lead
//         */
//        $lead = factory(Lead::class)->create();
//
//        $lead = $lead->cancel(Lead::PAID, 'Отменен');
//
//        $this->assertEquals(Lead::APPROVED, $lead['status'], 'Status set');
//        $this->assertEquals(Lead::PAID, $lead['sub_status_id'], 'Substatus id set');
//        $this->assertNotNull($lead->processed_at, 'Установлена дата обработки');
//        $this->assertEquals(1, $lead['is_hold'], 'Установлен флаг холда');
//        $this->assertEquals($lead->target_geo['hold_time'], $lead['hold_time'], 'Установлено время холда');
//    }
}