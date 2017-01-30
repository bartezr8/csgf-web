<?php
    return [
        'driver'          => 'centrifugo',
        'transport'       => 'redis', // http || redis
        'redisConnection' => 'centrifugo', // only for redis
        'baseUrl'         => 'http://beta.mh00.net:8000/api/', // api url
        'secret'          => '506070aa-c147-4927-9af3-c558770d3604', // you super secret key
    ];
