<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Classes\ElasticSchema;
use App\Events\Event;
use App\Events\Flow\FlowCreated;
use App\Events\Flow\FlowEdited;
use App\Models\Flow;
use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @todo remove when offer deletes
 */
class FlowElasticIndex implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Event|FlowEdited|FlowCreated $event
     */
    public function handle(Event $event)
    {
        $this->index($event->flow);
    }

    public function index(Flow $flow)
    {
        $params = [
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::FLOW_TYPE,
            'id' => $flow['id'],
            'body' => ['title' => $flow['title']]
        ];

        $this->client->index($params);
    }
}
