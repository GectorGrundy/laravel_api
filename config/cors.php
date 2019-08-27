<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel CORS
     |--------------------------------------------------------------------------
     |

     | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
     | to accept any value.
     |
     */
    'supportsCredentials' => true,
    'allowedOrigins' => [
        'http://localhost:8081',
        sprintf('http://%s', env('MAIN_DOMAIN')),
        sprintf('https://%s', env('MAIN_DOMAIN')),
        sprintf('http://my.%s', env('MAIN_DOMAIN')),
        sprintf('https://my.%s', env('MAIN_DOMAIN')),
        sprintf('http://control.%s', env('MAIN_DOMAIN')),
        sprintf('https://control.%s', env('MAIN_DOMAIN')),
        sprintf('http://office.%s', env('MAIN_DOMAIN')),
        sprintf('https://office.%s', env('MAIN_DOMAIN')),
        sprintf('http://support.%s', env('MAIN_DOMAIN')),
        sprintf('https://support.%s', env('MAIN_DOMAIN')),
        sprintf('http://manager.%s', env('MAIN_DOMAIN')),
        sprintf('https://manager.%s', env('MAIN_DOMAIN')),
    ],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 3600,
    'hosts' => [],
];

