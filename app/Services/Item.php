<?php 
namespace App\Services;

use App\Http\Controllers\GameController;
use App\Http\Controllers\SteamController;

use App\Item_BP;
use App\Item_Fast;
use App\Item_Steam;

use Exception;
use Storage;
use Cache;

class Item {
    
    public  $price;
    public  $mhn;
    public function __construct($info)
    {
        $this->market_hash_name = $info['market_hash_name'];
        $this->price = $this->getItemPrice($this->market_hash_name);
    }
    private function curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
    public static function getItemPrice($item_name) {
        $price_item = false; $count = 0;
        $si = Item_Steam::where('market_hash_name', $item_name)->first();
        $fi = Item_Fast::where('market_hash_name', $item_name)->first();
        $bi = Item_BP::where('market_hash_name', $item_name)->first();
        if(self::pchk($si)){
            $price_item += $si->price;
            if($si->price < 20){
                return $price_item;
            } else {
                $count++;
            }
        }
        if(self::pchk($fi)){
            $price_item += $fi->price;
            $count++;
        }
        if(self::pchk($bi)){
            $price_item += $bi->price;
            $count++;
        }
        if($count > 0) $price_item = round($price_item / $count, 2);
        return $price_item;
    }
    public static function pchk($item){
        $item = (object)$item;
        if(is_null($item)) return false;
        if(!isset($item->price)) return false;
        if(!$item->price) return false;
        if($item->price <= 0) return false;
        if((strrpos(strtolower(' '.$item->market_hash_name.' '), "souvenir") !== false)||(strrpos(strtolower(' '.$item->market_hash_name.' '), "Сувенир")) !== false) return false;
        return true;
    }
}