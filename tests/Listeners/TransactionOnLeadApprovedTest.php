<?php
declare(strict_types=1);

namespace Tests\Listeners;

use App\Events\Lead\LeadApproved;
use App\Listeners\OnLeadApproved;
use App\Models\HourlyStat;
use App\Models\Lead;
use App\Models\Order;

class TransactionOnLeadApprovedTest extends \Tests\TestCase
{
    /** @test */
    public function hourly_stat_updates_after_new()
    {
        factory(Order::class)->create()->each(function ($order) {
            $order->lead()->save(factory(Lead::class)->make());
        });
        $lead = Lead::all()[0];

        (new OnLeadApproved())->handle(new LeadApproved($lead, Lead::NEW));
        $hourly_stat = HourlyStat::all()[0];

        $this->assertEquals(0, $hourly_stat['held_count']);
        $this->assertEquals(0, $hourly_stat['onhold_payout']);
        $this->assertEquals(1, $hourly_stat['approved_count']);
        $this->assertEquals(1, $hourly_stat['leads_payout']);
        $this->assertEquals($lead['profit'], $hourly_stat['profit']);
    }
}