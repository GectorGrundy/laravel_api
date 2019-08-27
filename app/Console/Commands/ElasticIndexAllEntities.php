<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Listeners\User\UserElasticIndex;
use App\Models\{
    Flow, Offer, User
};
use Illuminate\Console\Command;
use App\Classes\ElasticSchema;
use App\Listeners\FlowElasticIndex;
use App\Listeners\OfferElasticIndex;

class ElasticIndexAllEntities extends Command
{
    protected $signature = 'elastic:index_all';
    protected $description = 'Adding all entities to elastic';

    public function handle()
    {
        ElasticSchema::dropIndex();

        $this->indexFlows();
        $this->indexOffers();
        $this->indexUsers();
        $this->call('api_methods:index');
    }

    private function indexFlows()
    {
        /**
         * @var FlowElasticIndex $flow_indexer
         */
        $flow_indexer = app(FlowElasticIndex::class);
        Flow::orderBy('id')->chunk(100, function ($flows) use ($flow_indexer) {
            foreach ($flows as $flow) {
                $flow_indexer->index($flow);
            }
        });
    }

    private function indexOffers()
    {
        /**
         * @var OfferElasticIndex $offer_indexer
         */
        $offer_indexer = app(OfferElasticIndex::class);
        Offer::orderBy('id')->chunk(100, function ($offers) use ($offer_indexer) {
            foreach ($offers as $offer) {
                $offer_indexer->index($offer);
            }
        });
    }

    private function indexUsers()
    {
        /**
         * @var UserElasticIndex $user_indexer
         */
        $user_indexer = app(UserElasticIndex::class);
        User::chunk(100, function ($users) use ($user_indexer) {
            foreach ($users as $user) {
                $user_indexer->index($user);
            }
        });
    }
}
