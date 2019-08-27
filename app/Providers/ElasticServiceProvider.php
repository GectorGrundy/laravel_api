<?php
declare(strict_types=1);

namespace App\Providers;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $hosts = [
            [
                'host' => 'elasticsearch',
//                'port' => '9200',
//                'scheme' => 'http',
//                'user' => 'elasticsearch',
//                'pass' => 'password!#$?*abc'
            ]];


        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setLogger(ClientBuilder::defaultLogger(storage_path('logs/elastic.log')))
            ->build();

        $this->app->instance(Client::class, $client);
    }
}
