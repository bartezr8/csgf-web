<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = 'shop';

    public $timestamps = false;

    protected $fillable = ['name', 'inventoryId', 'classid', 'price', 'bot_id', 'steam_price', 'rarity', 'quality', 'type'];

    const ITEM_STATUS_FOR_SALE = 0;
    const ITEM_STATUS_SOLD = 1;
    const ITEM_STATUS_NOT_FOUND = 2;
    const ITEM_STATUS_SEND = 3;
    const ITEM_STATUS_ERROR_TO_SEND = 4;
    const ITEM_STATUS_RETURNED = 5;

    public function buyer()
    {
        return $this->belongsTo('App\User', 'buyer_id', 'id');
    }

    public static function countItems()
    {
        return self::where('status', Shop::ITEM_STATUS_FOR_SALE)->count();
    }

    public static function countItem($classid){
        return self::where('status', Shop::ITEM_STATUS_FOR_SALE)->where('classid', $classid)->count();
    }

    public static function getClassRarity($type){
        switch ($type) {
            case 'Армейское качество':      return 'milspec'; break;
            case 'Запрещенное':             return 'restricted'; break;
            case 'Засекреченное':           return 'classified'; break;
            case 'Тайное':                  return 'covert'; break;
            case 'Ширпотреб':               return 'common'; break;
            case 'Промышленное качество':   return 'common'; break;
        }
    }
    public static function selectBot(){
        $max = 1000; $botid = 0;
        foreach (config('mod_shop.bots') as $bot_id => $bot){
            $count = self::where('status', self::ITEM_STATUS_FOR_SALE)->where('bot_id', $bot_id)->count();
            if($count < $max) {
                $max = $count;
                $botid = $bot_id;
            }
        }
        return $botid;
    }
    public static function parceTradeLinkShop($trade_link){
        $query_str = parse_url($trade_link, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        $response = ['accessToken' => $query_params['token'], 'steamid64' => (intval($query_params['partner']) + 76561197960265728)];
        return $response;
    }
}
