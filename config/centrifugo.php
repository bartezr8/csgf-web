<?php
//config('centrifugo.params')
return [
    'endpoint' => 'http://beta.mh00.net:8000/api/',
    'secret' => '506070aa-c147-4927-9af3-c558770d3604',
    'params' => [
        /*'redis' => [
            'host'          => 'localhost',
            'port'          => 6379,
            'db'            => 0,
            'timeout'       => 0.0,
            'shardsNumber'  => 0
        ],*/
        'http' => [
            // Curl options
            CURLOPT_TIMEOUT => 5
        ]
    ]
];

