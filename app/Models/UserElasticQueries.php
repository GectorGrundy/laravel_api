<?php
declare(strict_types=1);

namespace App\Models;

use App\Classes\ElasticSchema;
use Elasticsearch\Client;

class UserElasticQueries
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function findIdsByEmail($email)
    {
        $response = $this->client->search([
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::USER_TYPE,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $email,
                        'type' => 'phrase_prefix',
                        'fields' => ['email^2', 'email_analyzer']
                    ]
                ]
            ]
        ]);

        $user_ids = [];
        if (isset($response['hits']['total']) && $response['hits']['total'] > 0) {
            $user_ids = collect($response['hits']['hits'])->pluck('_id')->transform(function ($id) {
                return (int)$id;
            })->toArray();
        }

        return $user_ids;
    }
}