<?php namespace App\Services;

use App\Http\Controllers\GameController;
use App\Http\Controllers\SteamController;
use Exception;
use Storage;
use Cache;

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
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
    
    private function getStemItemPrice($mhn){
        sleep(5);
        $lowest = 0; $median=0;
        $tprice = self::curl('http://steamcommunity.com/market/priceoverview/?currency=5&country=ru&appid='.config('mod_game.appid').'&market_hash_name=' . urlencode($mhn) . '&format=json');
        $tprice = json_decode($tprice);
        if (isset($tprice->success)){
            \Log::error('OK');
            if (isset($tprice->lowest_price))$lowest = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->lowest_price)));
            if (isset($tprice->median_price))$median = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->median_price)));
            if($lowest<$median){ 
                return $lowest;
            }else{
                return $median;
            }
        }
		return false;
	}
    public function getItemPrice() {
        $price_item = false;
        $item_name = $this->market_hash_name;
        $price_item = 0;
        $usd = self::getActualCurs();
        
        $BPitems = Cache::get('bp_market_prices');
        $FastItems = Cache::get('fast_market_prices');
        $SItems = Cache::get('steam_market_prices');
        
        $fprice = 0; $bprice = 0; $sprice = 0;
        if(isset($SItems[$item_name])){
            $sprice = round($SItems[$item_name] * $usd, 2);
        } else {
            $sprice = round(self::getStemItemPrice($item_name), 2);
        }
        if(isset($FastItems->$item_name)) $fprice = round($FastItems->$item_name * $usd, 2);
        if(isset($BPitems->$item_name)) $bprice = round($BPitems->$item_name->value * $usd / 100, 2);
        $del_on = 0;
        if($sprice > 0){
            $price_item += $sprice;
            $del_on++;
        } 
        if($fprice > 0){
            $price_item += $fprice;
            $del_on++;
        }
        if($bprice > 0){
            $price_item += $bprice;
            $del_on++;
        }
        if($del_on > 0){
            $price_item = round($price_item / $del_on, 2);
            if($price_item < 30){
                if($sprice > 0){
                    $price_item = $sprice;
                }
            } else if($price_item > 20000){
                if($fprice > 0){
                    if($bprice > 0){
                        $price_item = round(($fprice + $bprice)/2, 2);
                    } else {
                        $price_item = $fprice;
                    }
                } else if($bprice > 0){
                    $price_item = $bprice;
                } else {
                    $price_item = $sprice;
                }
            }
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
			$usd = Cache::get('ActualCurs');
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