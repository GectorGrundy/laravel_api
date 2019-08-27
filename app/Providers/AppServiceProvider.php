<?php
declare(strict_types=1);

namespace App\Providers;

use App\Http\GoDataContainer;
use App\Models\{
    Administrator, CashRequisite, Deposit, FlowDomain, EpaymentsRequisite, Integration, Landing, Lead, MyOffer, PaxumRequisite, Publisher, RedirectDomain, SwiftRequisite, TdsDomain, Transit, WebmoneyRequisite
};
use App\Services\Cloaking\ParserSettings;
use App\Services\PublisherApiDataContainer;
use Illuminate\Support\ServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Dusk\DuskServiceProvider;
use Sentry\SentryLaravel\SentryLaravelServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->morphMapRelations();

        if (config('env.log_queries', false)) {
            dbd($this->app['log']);
        }
    }

    public function register()
    {
        $env = $this->app->environment();

        if ($env === 'local') {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(DuskServiceProvider::class);
        }

        if ($this->app->environment('production')) {
            $this->app->register(SentryLaravelServiceProvider::class);
        }

        $this->app->singleton(GoDataContainer::class, function () {
            return new GoDataContainer();
        });
        $this->app->singleton(PublisherApiDataContainer::class, function () {
            return new PublisherApiDataContainer();
        });
        $this->app->singleton(ParserSettings::class, function () {
            return new ParserSettings();
        });
    }

    private function morphMapRelations()
    {
        Relation::morphMap([
            'landing' => Landing::class,
            'transit' => Transit::class,
            'tds' => TdsDomain::class,
            'redirect' => RedirectDomain::class,
            'flow' => FlowDomain::class,
            'my_offer' => MyOffer::class,
            'webmoney' => WebmoneyRequisite::class,
            'epayments' => EpaymentsRequisite::class,
            'paxum' => PaxumRequisite::class,
            'swift' => SwiftRequisite::class,
            'lead' => Lead::class,
            'cash' => CashRequisite::class,
            'deposit' => Deposit::class,
            'integration' => Integration::class,
            'administrator' => Administrator::class,
            'publisher' => Publisher::class,
        ]);
    }
}
