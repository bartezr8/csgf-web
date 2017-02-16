<?php

get('/', ['as' => 'index', 'uses' => 'GameController@currentGame']);
get('/login', ['as' => 'login', 'uses' => 'SteamController@login']);
get('/auth', ['as' => 'auth', 'uses' => 'SteamController@auth']);
get('/support', ['as' => 'support', 'uses' => 'PagesController@support']);
get('/fairplay/{game}', ['as' => 'fairplay', 'uses' => 'PagesController@fairplay']);
get('/fairplay', ['as' => 'fairplay_no', 'uses' => 'PagesController@fairplay_no']);
get('/top', ['as' => 'top', 'uses' => 'PagesController@top']);
get('/game/{game}', ['as' => 'game', 'uses' => 'PagesController@game']);
get('/user/{user}', ['as' => 'user', 'uses' => 'PagesController@user']);
get('/history', ['as' => 'history', 'uses' => 'PagesController@history']);
get('/escrow', ['as' => 'escrow', 'uses' => 'PagesController@escrow']);
get('/chat', ['as' => 'chat', 'uses' => 'ChatController@chat']);
get('/rand_url', ['as' => 'rand_url', 'uses' => 'PagesController@rand_url']);
get('/success', 'PagesController@success');
get('/fail', 'PagesController@fail');
post('/donate', 'DonateController@Donate');
post('/getSlimit', ['as' => 'get.slimit', 'uses' => 'GameController@getSlimit']);
post('ajax', ['as' => 'ajax', 'uses' => 'AjaxController@parseAction']);

Route::group(['middleware' => 'auth'], function () {
    get('/logout', ['as' => 'logout', 'uses' => 'SteamController@logout']);
    get('/ref', ['as' => 'ref', 'uses' => 'RefController@ref']);    
    get('/getcoupon', ['as' => 'getcoupon', 'uses' => 'RefController@getcoupon']);
    get('/setcoupon', ['as' => 'setcoupon', 'uses' => 'RefController@setcoupon']);
    get('/pay', ['as' => 'pay', 'uses' => 'PagesController@pay']);
    get('/dep', ['as' => 'deposit', 'uses' => 'GameController@deposit']);
    get('/my-inventory', ['as' => 'my-inventory', 'uses' => 'PagesController@myinventory']);
    post('/myinventory', ['as' => 'myinventory', 'uses' => 'PagesController@myinventory']);
    post('/getcoupon', ['as' => 'getcoupon', 'uses' => 'RefController@getcoupon']);
    post('/setcoupon', ['as' => 'setcoupon', 'uses' => 'RefController@setcoupon']);
    post('/settings/save', ['as' => 'settings.update', 'uses' => 'SteamController@updateSettings']);
    post('/addTicket', ['as' => 'add.ticket', 'uses' => 'GameController@addTicket']);
    post('/getBalance', ['as' => 'get.balance', 'uses' => 'GameController@getBalance']);
    post('/my_comission', 'GameController@curcomm');
    post('/updatepassword', ['as' => 'updatepassword', 'uses' => 'SteamController@updatepassword']);
});

/* GIVEOUT ROUTES */
Route::group(['middleware' => 'auth'], function () {
    get('/out', ['as' => 'out_index', 'uses' => 'GiveOutController@out_index']);
    post('/out/start', 'GiveOutController@startOut');
    post('/out/get', 'GiveOutController@getOut');
    post('/out/getMon', 'GiveOutController@getMon');
});
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/out/check', ['as' => 'checkUsers', 'uses' => 'GiveOutController@checkUsers']);
});

/* GIFT ROUTES */
Route::group(['middleware' => 'auth'], function () {
    get('/gifts/receive', ['as' => 'receiveGift', 'uses' => 'GiftsController@receiveGift']);
    get('/gifts/admin', ['as' => 'gift_admin', 'uses' => 'GiftsController@gift_admin', 'middleware' => 'access:admin']);
    get('/gifts/admin/select', ['as' => 'selectGiftWinner', 'uses' => 'GiftsController@selectGiftWinner', 'middleware' => 'access:admin']);
});

Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/gifts/check', ['as' => 'checkWinners', 'uses' => 'GiftsController@checkWinners']);
});

/* SCRIPT ROUTES */
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/chat', ['as' => 'chat', 'uses' => 'ChatController@chat']);
    post('/update', 'GameController@update');
    post('/checkOffer', 'GameController@checkOffer');
    post('/newBet', 'GameController@newBet');
    post('/setGameStatus', 'GameController@setGameStatus');
    post('/setPrizeStatus', 'GameController@setPrizeStatus');
    post('/getCurrentGame', 'GameController@getCurrentGame');
    post('/getWinners', 'GameController@getWinners');
    post('/getPreviousWinner', 'GameController@getPreviousWinner');
    post('/novigra', 'GameController@newGame');
    post('/checkBrokenGames', 'GameController@checkBrokenGames');
    post('/userinfo', 'GameController@userinfo');
});

/* SHOP ROUTES */
get('/shop', ['as' => 'shop', 'uses' => 'ShopController@index']);
post('/shop/items', ['as' => 'shop_items', 'uses' => 'ShopController@shop']);
Route::group(['middleware' => 'auth'], function () {
    get('/shop/deposit', ['as' => 'shop_deposit', 'uses' => 'ShopController@deposit']);
    get('/shop/history', ['as' => 'cards-history', 'uses' => 'ShopController@history']);
    post('/shop/buy', ['as' => 'settings.update', 'uses' => 'ShopController@buyItem']);
    post('/shop/getcart', ['as' => 'getcart', 'uses' => 'ShopController@getcart']);
    post('/shop/sellitems', ['as' => 'sellitems', 'uses' => 'ShopController@sellitems']);
    post('/shop/myinventory', ['as' => 'cards-myinventory', 'uses' => 'ShopController@myinventory']);
    post('/shop/inv_update', ['as' => 'inv_update', 'uses' => 'ShopController@inv_update']);
    post('/shop/checkOffers', ['as' => 'shop_checkOffers', 'uses' => 'ShopController@checkOffers']);
    post('/shop/buySale', ['as' => 'shop_buySale', 'uses' => 'ShopController@buySale']);
});
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/shop/checkAllOffers', 'ShopController@checkAllOffers');
    post('/shop/newItems', 'ShopController@addItemsToSale');
    post('/shop/itemlist', 'ShopController@itemlist');
    post('/shop/checkShop', 'ShopController@checkShop');
    post('/shop/setItemStatus', 'ShopController@setItemStatus');
    post('/shop/deposit/toCheck', 'ShopController@depositToCheck');
    post('/shop/deposit/check', 'ShopController@depositCheck');
});
Route::group(['middleware' => 'auth'], function () {
    post('/shop/admin/clearShop', ['as' => 'shop_admin', 'uses' => 'ShopController@clearShop', 'middleware' => 'access:admin']);
    post('/shop/admin/updateDep', ['as' => 'shop_admin', 'uses' => 'ShopController@updateSTrade', 'middleware' => 'access:admin']);
    post('/shop/admin/updateShop', ['as' => 'give', 'uses' => 'ShopController@updateShop', 'middleware' => 'access:moderator']);
    get('/shop/admin', ['as' => 'shop_admin', 'uses' => 'ShopController@admin', 'middleware' => 'access:moderator']);
});

/* CHAT ROUTES */
Route::group(['middleware' => 'auth'], function () {
    post('/add_message', ['as' => 'chat', 'uses' => 'ChatController@add_message']);
    post('/delmsg', ['as' => 'chat', 'uses' => 'ChatController@delmsg']);
    post('/chat', ['as' => 'chat', 'uses' => 'ChatController@chat']);
});

/* ADMIN ROUTES */
Route::group(['middleware' => 'auth'], function () {
    get('/admin', ['as' => 'give', 'uses' => 'AdminController@admin', 'middleware' => 'access:moderator']);
    post('/admin/ctime', ['as' => 'give', 'uses' => 'AdminController@ctime', 'middleware' => 'access:admin']);
    post('/admin/winner', ['as' => 'give', 'uses' => 'AdminController@winner', 'middleware' => 'access:admin']);
    post('/admin/winnerr', ['as' => 'give', 'uses' => 'AdminController@winnerr', 'middleware' => 'access:admin']);
    post('/admin/sendgame', ['as' => 'give', 'uses' => 'GameController@fixRequest', 'middleware' => 'access:admin']);
    post('/admin/fixtic', ['as' => 'give', 'uses' => 'AdminController@fixGameTic', 'middleware' => 'access:admin']);
    post('/admin/clearQueue', ['as' => 'give', 'uses' => 'AdminController@clearQueue', 'middleware' => 'access:admin']);
    post('/admin/cleartables', ['as' => 'give', 'uses' => 'AdminController@cleartables', 'middleware' => 'access:admin']);
    post('/admin/addparser', ['as' => 'give', 'uses' => 'AdminController@addparser', 'middleware' => 'access:admin']);
    /* ADMIN CENSURE */
    get('/admin/cens', ['as' => 'give', 'uses' => 'AdminController@cens', 'middleware' => 'access:moderator']);    
    post('/admin/cens/getwords', ['as' => 'give', 'uses' => 'AdminController@getwords', 'middleware' => 'access:moderator']);
    post('/admin/cens/add', ['as' => 'give', 'uses' => 'AdminController@addword', 'middleware' => 'access:moderator']);
    /* ADMIN USERS */
    get('/admin/users', ['as' => 'give', 'uses' => 'AdminController@users', 'middleware' => 'access:moderator']);    
    get('/admin/user/{user}', ['as' => 'give', 'uses' => 'AdminController@user', 'middleware' => 'access:moderator']);    
    post('/admin/userinfo', ['as' => 'give', 'uses' => 'AdminController@userinfo', 'middleware' => 'access:moderator']);
    post('/admin/users/updateNick', ['as' => 'give', 'uses' => 'AdminController@updateUNick', 'middleware' => 'access:moderator']);
    post('/admin/users/updateMute', ['as' => 'give', 'uses' => 'AdminController@updateMute', 'middleware' => 'access:moderator']);
    post('/admin/users/updateBan', ['as' => 'give', 'uses' => 'AdminController@updateBan', 'middleware' => 'access:moderator']);
    post('/admin/users/updateBanSup', ['as' => 'give', 'uses' => 'AdminController@updateBanSup', 'middleware' => 'access:moderator']);
    post('/admin/users/updateMoney', ['as' => 'give', 'uses' => 'AdminController@updateMoney', 'middleware' => 'access:admin']);
    post('/admin/users/updateSlimit', ['as' => 'give', 'uses' => 'AdminController@updateSlimit', 'middleware' => 'access:admin']);
    post('/admin/users/updateAdmin', ['as' => 'give', 'uses' => 'AdminController@updateAdmin', 'middleware' => 'access:admin']);
    post('/admin/users/updateModerator', ['as' => 'give', 'uses' => 'AdminController@updateModerator', 'middleware' => 'access:admin']);
    /* ADMIN FUSER*/
    post('/admin/fuser_add', ['as' => 'give', 'uses' => 'AdminController@fuser_add', 'middleware' => 'access:admin']);
    post('/admin/fuser_del', ['as' => 'give', 'uses' => 'AdminController@fuser_del', 'middleware' => 'access:admin']);
});

/* SEND ROUTES */
Route::group(['middleware' => 'auth'], function () {
    post('/send', ['as' => 'send', 'uses' => 'SendController@send']);
    post('/send/list', ['as' => 'sendlist', 'uses' => 'SendController@sendlist']);
});

/* DOUBLE ROUTES */
get('/double', ['as' => 'double_index', 'uses' => 'DoubleController@double_index']);
Route::group(['middleware' => 'auth'], function () {
    post('/admin/double', ['as' => 'dwinner', 'uses' => 'DoubleController@dwinner', 'middleware' => 'access:admin']);
    post('/double/bet', ['as' => 'bet', 'uses' => 'DoubleController@newBet']);    
});

Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/double/newGame', 'DoubleController@newGame');
    post('/double/startGame', 'DoubleController@startGame');
    post('/double/setGameStatus', 'DoubleController@setGameStatus');
    post('/double/getCurrentGame', 'DoubleController@getCurrentGame');
});
/* COINFLIP ROUTES */
get('/coin', ['as' => 'coinflip', 'uses' => 'CoinFlipController@index']);
Route::group(['middleware' => 'auth'], function () {
    post('/coin/bet', ['as' => 'coinflip_bet', 'uses' => 'CoinFlipController@bet']);
    post('/coin/nbet', ['as' => 'coinflip_new_bet', 'uses' => 'CoinFlipController@nbet']);
});
/* BICH GAME */
get('/bich', ['as' => 'bich', 'uses' => 'BGameController@currentGame']);
Route::group(['middleware' => 'auth'], function () {
    get('/bdep', ['as' => 'bdeposit', 'uses' => 'BGameController@deposit']);
});
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/bcheckOffer', 'BGameController@checkOffer');
    post('/newBBet', 'BGameController@newBet');
    post('/bsetPrizeStatus', 'BGameController@setPrizeStatus');
    post('/bgetWinners', 'BGameController@getWinners');
    post('/newBgame', 'BGameController@newGame');
    post('/checkBrokenBGames', 'BGameController@checkBrokenGames');
});
/* DICE ROUTES */
get('/dice', ['as' => 'dicegame', 'uses' => 'DiceController@index']);
Route::group(['middleware' => 'auth'], function () {
    post('/dice/bet', ['as' => 'dicebet', 'uses' => 'DiceController@bet']);
});
/* VK ROUTES */
Route::group(['prefix' => 'api'], function () {
    post('/vk', 'VKController@index');
    post('/vk/sendText', 'VKController@sendTextVK');
});
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/vk/checkSending', 'VKController@checkSending');
});
/* PARSER ROUTES */
get('/prices', 'ParserController@prices');
Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    post('/parseSteam', 'ParserController@parseSteam');
});
