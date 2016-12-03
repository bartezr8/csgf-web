<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Services\Item;
use Cache;

use App\Item_BP;
use App\Item_Fast;
use App\Item_Steam;

class UpdatePrices extends Command
{
    const COURSE = 65;
    const BANK_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';
    
    const BP_URL = 'http://backpack.tf/api/IGetMarketPrices/v1/?key=';
    const FAST_URL = 'https://api.csgofast.com/price/all';
    
    protected $signature = 'prices:update {--bp} {--fast} {--steam}';

    protected $description = ' {--bp} {--fast} {--steam} Updates Prices from steam, bp and fast';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $this->log('Loading prices');
        $usd = $this->getActualCurs();
        if($this->option('bp')){
            $this->log('Getting prices from BackPack');
            $dataBP = self::curl(self::BP_URL . config('mod_game.backpack_key') . '&compress=1&appid=' . config('mod_game.appid'));
            $response = json_decode($dataBP);
            if(isset($response->response->items)){
                $BPitems = $response->response->items;
                $count = 0;
                foreach($BPitems as $key => $item){
                    $nitem = [ 'market_hash_name' => $key, 'price' => $item->value / 100 * $usd ];
                    $dbitem = Item_BP::where('market_hash_name', $nitem['market_hash_name'])->first();
                    if(Item::pchk($nitem)){
                        if(is_null($dbitem)){
                            Item_BP::create($nitem);
                        } else {
                            $dbitem->price = $nitem['price'];
                            $dbitem->save();
                        }
                        $this->log($nitem['market_hash_name'].' : '.$nitem['price']);
                        $count++;
                        if($count>20){
                            $count = 0;
                            sleep(1);
                        }
                    }
                }
                $this->log('BP prices parsed');
            }
        }
        if($this->option('fast')){
            $this->log('Getting prices from CSGOFAST');
            $jsonItemsFast = self::curl(self::FAST_URL);
            $FastItems = json_decode($jsonItemsFast, true);
            if(isset($FastItems['AWP | Dragon Lore (Factory New)'])){
                $count = 0;
                foreach($FastItems as $key => $item){
                    $nitem = [ 'market_hash_name' => $key, 'price' => $item * $usd ];
                    $dbitem = Item_Fast::where('market_hash_name', $nitem['market_hash_name'])->first();
                    if(Item::pchk($nitem)){
                        if(is_null($dbitem)){
                            Item_Fast::create($nitem);
                        } else {
                            $dbitem->price = $nitem['price'];
                            $dbitem->save();
                        }
                        $this->log($nitem['market_hash_name'].' : '.$nitem['price']);
                        $count++;
                        if($count>20){
                            $count = 0;
                            sleep(1);
                        }
                    }
                }
                $this->log('FAST prices parsed');
            }
        }
        if($this->option('steam')){
            $items = Item_Steam::where('price', '<', 0.5)->get();
            foreach($items as $item){
                sleep(15);
                $nitem = Item_Steam::where('market_hash_name', $item->market_hash_name)->first();
                $nitem->price = $this->getStemItemPrice($item->market_hash_name);
                $nitem->save();
                $this->log($nitem->market_hash_name.' : '.$nitem->price);
            }
        }
    } 
    private function getStemItemPrice($mhn){
        $lowest = 0; $median=0;
        $tprice = self::curl('http://steamcommunity.com/market/priceoverview/?currency=5&country=ru&appid='.config('mod_game.appid').'&market_hash_name=' . urlencode($mhn) . '&format=json');
        $tprice = json_decode($tprice);
        if (isset($tprice->success)){
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
    private function getActualCurs() {
        if(!Cache::has('ActualCurs')) {
            $link = self::BANK_URL;
            $str  = self::curl($link);
            preg_match('#<Valute ID="R01235">.*?.<Value>(.*?)</Value>.*?</Valute>#is', $str, $value);
            $usd = $value[1];
            Cache::put('ActualCurs', $usd, 6 * 60 * 60);
        } else {
			$usd = Cache::get('ActualCurs');
        }
        if(!$usd) $usd = self::COURSE;
        return $usd;
    }
    private function log($text) {
        echo sprintf("[%s] %s\r\n", Carbon::now()->toDayDateTimeString(), $text);
    }
}