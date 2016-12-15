<?php
return [
    'factor' => 20,                     // % дополнительного начисления.
    'vip_only' => true,                 // Начислять только випам.
    'freekassa_id' => env('PAY_ID'),    //
    'freekassa_s1' => env('PAY_SEC1'),  //
    'freekassa_s2' => env('PAY_SEC2')   //
];
