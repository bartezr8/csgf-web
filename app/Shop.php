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
    
    const D_DEPOSIT = 0;
    const D_BUY = 1;
    const D_RETURN = 2;
    const D_MONEY = 3;

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
        if (!\Cache::has('last.shop')) {
            foreach (config('mod_shop.bots') as $bot_id => $bot){
                $count = self::where('status', self::ITEM_STATUS_FOR_SALE)->where('bot_id', $bot_id)->count();
                if($count < $max) {
                    $max = $count;
                    $botid = $bot_id;
                }
            }
        } else {
            $last = \Cache::get('last.shop');
            if($last + 1 > (count(config('mod_shop.bots')) - 1)){
                $botid = 0;
            } else {
                $botid = $last + 1;
            }
        }
        \Cache::put('last.shop', $botid, 60);        
        return $botid;
    }
    public static function parceTradeLinkShop($trade_link){
        $query_str = parse_url($trade_link, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        $response = ['accessToken' => $query_params['token'], 'steamid64' => (intval($query_params['partner']) + 76561197960265728)];
        return $response;
    }
}
