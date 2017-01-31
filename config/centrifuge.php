<?php
    return [
        'driver'          => 'centrifugo', // redis channel name as provided in cent. conf ($driver.".api")
        'transport'       => env('CENT_TRANSPORT_API'), // http || redis connection, check more information below
        'redisConnection' => 'centrifugo', // only for redis, name of connection more information below
        'baseUrl'         => env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), // full api url
        'secret'          => env('CENT_SECRET'), // you super secret key
    ];
