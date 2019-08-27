<?php
declare(strict_types=1);

namespace Tests\Strategies;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RefreshDatabase extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function init()
    {
        \Artisan::call('db:reset');
    }
}