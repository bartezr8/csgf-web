<?php
//config('mod_shop.shop_strade_secret')
return [
	'shop' => true,
	
	'max_daily' => true,
	'max_daily_sum' => 20, //per each level
	
	'items_per_trade' => 100, 
	
	'games_need' => true,
	'games_need_count' => 1,

	'steam_price_%' => 100,
	'dep_comission_from' => 50,
	'dep_comission_%' => 5,
	
	'garbadge_from' => 10,
	'garbadge_%' => 30,
	
	'shop_strade_port' => '80',
	'shop_strade_ip' => '46.105.42.220'
];
