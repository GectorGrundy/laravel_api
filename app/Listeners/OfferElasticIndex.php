<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Classes\ElasticSchema;
use App\Events\Event;
use App\Events\Offer\OfferCreated;
use App\Events\Offer\OfferEdited;
use App\Models\Offer;
use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @todo remove when offer deletes
 */
class OfferElasticIndex implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Event|OfferEdited|OfferCreated $event
     */
    public function handle(Event $event)
    {
        $this->index($event->offer);
    }

    public function index(Offer $offer)
    {
        $params = [
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::OFFER_TYPE,
            'id' => $offer['id'],
            'body' => [
                'title' => $offer['title'],
                'description' => $offer['description'],
            ]
        ];

        $this->client->index($params);
    }
}
