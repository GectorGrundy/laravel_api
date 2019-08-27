<?php
declare(strict_types=1);

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends DuskTestCase
{
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://affninja.app')
                ->clickLink('Войти')
                ->waitFor('#Modal')
                ->type('#emailInput', env('TEST_PUBLISHER_EMAIL'))
                ->type('#passwordInput', 'secret')
                ->click('#loginBtn')
                ->waitForText('Потоки');
        });
    }
}
