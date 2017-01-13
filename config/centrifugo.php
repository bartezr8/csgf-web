<?php
//config('centrifugo.params')
return [
    'endpoint' => 'http://beta.mh00.net:8000/api/',
    'secret' => '506070aa-c147-4927-9af3-c558770d3604',
    'params' => [
        'redis' => [
            'path'          => env('REDIS_PATH'),
            'host'          => env('REDIS_HOST'),
            'port'          => env('REDIS_PORT'),
            'password'      => env('REDIS_PASSWORD'),
            'db'            => 0,
            'timeout'       => 0.0,
            'shardsNumber'  => 0
        ],
        'http' => [
            // Curl options
            CURLOPT_TIMEOUT => 5
        ]
    ]
];

