<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Services\Item;
use Cache;

use App\Item_BP;
use App\Item_Fast;
use App\Item_Steam;

class Parser extends Command
{
    const COURSE = 65;
    const BANK_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';
    
    const BP_URL = 'http://backpack.tf/api/IGetMarketPrices/v1/?key=';
    const FAST_URL = 'https://api.csgofast.com/price/all';
    
    protected $signature = 'parser:prices {--bp} {--fast}';

    protected $description = ' {--bp} {--fast} Updates Prices from steam, bp and fast';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $this->log('Запускаем парсер');
        $usd = $this->getActualCurs();
        if(!$this->option('bp')&&!$this->option('fast')){
            $this->log('Неверные параметры! Проверьте правильность введенной команды!');
        } else {
            if($this->option('bp')){
                $this->log('Грузим цены с BackPack');
                $dataBP = self::curl(self::BP_URL . config('mod_game.backpack_key') . '&compress=1&appid=' . config('mod_game.appid'));
                $response = json_decode($dataBP);
                if(isset($response->response->items)){
                    $BPitems = $response->response->items; $count = 0; $num = 0; $last = 0;
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
                        }
                        $num++; 
                        $percent = round((($num*100)/count((array)$BPitems)),2);
                        if($percent>($last+0.99)){
                            $this->log($percent.'%');
                            $last = $percent;
                        }
                    }
                    $this->log('Цены с BackPack загружены');
                }
            }
            if($this->option('fast')){
                $this->log('Грузим цены с CSGOFAST');
                $jsonItemsFast = self::curl(self::FAST_URL);
                $FastItems = json_decode($jsonItemsFast, true);
                if(isset($FastItems['AWP | Dragon Lore (Factory New)'])){
                    $count = 0; $num = 0; $last = 0;
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
                        }
                        $num++; 
                        $percent = round((($num*100)/count((array)$BPitems)),2);
                        if($percent>($last+0.99)){
                            $this->log($percent.'%');
                            $last = $percent;
                        }
                    }
                    $this->log('Цены с CSGOFAST загружены');
                }
            }
        }
        $this->log('Парсер завершает свою работу');
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