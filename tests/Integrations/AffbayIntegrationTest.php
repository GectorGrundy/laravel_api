<?php
declare(strict_types=1);

namespace Tests\Integrations;

use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AffbayIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function cpl_lead_trashed_if_invalid_phone()
    {
        $lead = factory(Lead::class)->make();
    }
}