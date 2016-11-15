<?php namespace App\Services;

use App\Http\Controllers\GameController;
use App\Http\Controllers\SteamController;
use Exception;
use Storage;

class SteamItem {

    const BANK_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';
    const URL_REQUEST = 'http://backpack.tf/api/IGetMarketPrices/v1/?key=';

    public  $classid;
    public  $name;
    public  $market_hash_name;
    public  $price;
    public  $rarity;

    public function __construct($info)
    {
        $this->classid = $info['classid'];
        $this->name = $info['name'];
        $this->market_hash_name = $info['market_hash_name'];
        $this->rarity = isset($info['rarity']) ? $info['rarity'] : $this->getItemRarity($info);
        $this->price = $this->getItemPrice();
    }

    private function curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
    
    private function getStemItemPrice($mhn){
        sleep(1);
        $tprice = self::curl('http://steamcommunity.com/market/priceoverview/?currency=5&country=ru&appid='.config('mod_game.appid').'&market_hash_name=' . urlencode($mhn) . '&format=json');
        $tprice = json_decode($tprice);
        \Log::error('Item price 3: '.$tprice);
        if (isset($tprice->success)){
            \Log::error('OK');
            $lowest = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->lowest_price)));
            $median = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->median_price)));
            if($lowest<$median){ 
                \Log::error('Item price 3: '.$lowest);
                return $lowest;
            }else{
                \Log::error('Item price 3: '.$median);
                return $median;
            }
        }
		return false;
	}
    private function UpdateBP(){
        $data = self::curl(self::URL_REQUEST . config('mod_game.backpack_key') . '&compress=1&appid=' . config('mod_game.appid'));
        if(!$data) return false;
        $response = json_decode($data);
        if(!isset($response->response->success)) return false;
        if ($response->response->success != 0) {
            if(isset($response->response->items)){
                \Cache::put('BP_items', json_encode($response->response->items), 60* 60 * 12);
            }
            return true;
        }
        return false;
    }
    
    public function getBPItemPrice(){
        if(!\Cache::has('BP_items')) {
            self::UpdateBP();
        }
        if(\Cache::has('BP_items')) {
            $usd = $this->getActualCurs();
            $item_name = $this->market_hash_name;
            $price_item = 0;
            $items = json_decode(\Cache::get('BP_items'));
            
            if(isset($items->$item_name)){
                $item = $items->$item_name->value;
                $price_item = $item / 100 * $usd;
            }
        }
        return $price_item;
    }
    
    public function getItemPrice() {
        $price_item = false;
        if(\Cache::has('BP_items')) {
            $usd = $this->getActualCurs();
            $item_name = $this->market_hash_name;
            $price_item = 0;
            $items = \Cache::get('BP_items');
            
            if(!isset($items->$item_name)){
                $price_item = 0;
                \Log::error('isset');
            } else {
                \Log::error('!isset');
                $item = $items->$item_name->value;
                $price_item = $item / 100 * $usd;
            }
            \Log::error('Item price 1: '.$price_item);
            /*if(($price_item == 0) || $price_item < 10)*/ $price_item = self::getStemItemPrice($item_name);
            \Log::error('Item price 2: '.$price_item);
        }
        return $price_item;
    }

    public static function getActualCurs() {
        if(!\Cache::has('ActualCurs')) {
            $link = self::BANK_URL;
            $str  = file_get_contents($link);
            preg_match('#<Valute ID="R01235">.*?.<Value>(.*?)</Value>.*?</Valute>#is', $str, $value);
            $usd = $value[1];
            \Cache::put('ActualCurs', $usd, 12 * 60 * 60);
        } else {
			$usd = \Cache::get('ActualCurs');
        }
        if(!$usd) $usd = 65;
        return $usd;
    }

    public function getItemRarity($info) {
        $type = $info['type'];
        $rarity = '';
        $arr = explode(',',$type);
        if (count($arr) == 2) $type = trim($arr[1]);
        if (count($arr) == 3) $type = trim($arr[2]);
        if (count($arr) && $arr[0] == 'Нож') $type = '★';
        switch ($type) {
            case 'Армейское качество':      $rarity = 'milspec'; break;
            case 'Запрещенное':             $rarity = 'restricted'; break;
            case 'Засекреченное':           $rarity = 'classified'; break;
            case 'Тайное':                  $rarity = 'covert'; break;
            case 'Ширпотреб':               $rarity = 'common'; break;
            case 'Промышленное качество':   $rarity = 'common'; break;
            case '★':                       $rarity = 'rare'; break;
        }

        return $rarity;
    }

    private function _setToFalse()
    {
        $this->name = false;
        $this->price = false;
        $this->rarity = false;
    }
}