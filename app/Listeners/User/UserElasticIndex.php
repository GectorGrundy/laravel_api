<?php
declare(strict_types=1);

namespace App\Listeners\User;

use App\Classes\ElasticSchema;
use App\Events\User\UserCreated;
use App\Events\UserRegistered;
use App\Models\User;
use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class UserElasticIndex implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param UserRegistered|UserCreated $event
     */
    public function handle($event)
    {
        $this->index($event->user);
    }

    public function index(User $user)
    {
        $params = [
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::USER_TYPE,
            'id' => $user['id'],
            'body' => [
                'email' => $user['email'],
            ]
        ];

        $this->client->index($params);
    }
}
