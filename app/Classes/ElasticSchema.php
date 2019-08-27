<?php
declare(strict_types=1);

namespace App\Classes;

use Elasticsearch\Client;

class ElasticSchema
{
    public const INDEX = 'affninja';
    public const FLOW_TYPE = 'flow';
    public const OFFER_TYPE = 'offer';
    public const USER_TYPE = 'user';
    public const API_LOG_TYPE = 'api_log';

    public static $settings = [
        'index' => self::INDEX,
        'body' => [
            'settings' => [
                'analysis' => [
                    'filter' => [
//                        'russian_stop' => [
//                            'type' => 'stop',
//                            'stopwords' => '_russian_',
//                        ],
//                        'russian_keywords' => [
//                            'type' => 'keyword_marker',
//                            'keywords' => [],
//                        ],
                        'russian_stemmer' => [
                            'type' => 'stemmer',
                            'language' => 'russian',
                        ],
                    ],
                    'analyzer' => [
                        'default' => [
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
//                                'russian_stop',
//                                'russian_keywords',
                                'russian_stemmer'
                            ],
                        ],
                        'email_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'uax_url_email',
                            'filter' => ['lowercase', 'stop']
                        ]
                    ],
                ],
            ],
            'mappings' => [
                '_default_' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'default',
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'default',
                        ],
                    ]
                ],
                self::OFFER_TYPE => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'title' => [],
                        'description' => []
                    ]
                ],
                self::FLOW_TYPE => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'title' => []
                    ]
                ],
                self::USER_TYPE => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'search_analyzer' => 'email_analyzer',
                            'fields' => [
                                'email_not_splitted' => [
                                    'type' => 'string',
                                    'analyzer' => 'email_analyzer'
                                ]
                            ],
                        ]
                    ]
                ],
            ]
        ]
    ];

    public static function dropIndex()
    {
        $client = app(Client::class);

        if ($client->indices()->exists(['index' => ElasticSchema::INDEX])) {
            return $client->indices()->delete(['index' => ElasticSchema::INDEX]);
        }
    }

    /**
     * @unused
     */
    public static function refreshMapping()
    {
        return (new static)->_refreshMapping();
    }

    /**
     * @unused
     */
    private function _refreshMapping()
    {
        /**
         * @var Client $client
         */
        $client = app(Client::class);

        $client->indices()->delete(['index' => ElasticSchema::INDEX]);

        // Check for existing index
        if (!$exists = $client->indices()->exists(['index' => self::INDEX])) {
            $client->indices()->create(ElasticSchema::$settings);
        }
    }
}