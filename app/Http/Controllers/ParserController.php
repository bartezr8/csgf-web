<?php

namespace App\Http\Controllers;
use DB;
use Cache;
use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Item_BP;
use App\Item_Fast;
use App\Item_Steam;

use App\Services\Item;

class ParserController extends Controller
{
    public function prices(Request $request){
        $result = '';
        switch($request->get('type')){
            case 'default': $result = self::getDef(); break;
            case 'steam': $result = self::getSteam(); break;
            case 'no_steam': $result = self::getNSteam(); break;
            case 'no_souvenir': $result = self::getNSouvenir(); break;
            default: $result = self::getDef(); break;
        }
        return $result;
    }
    private function getDef(){
        $items = [];
        if(!Cache::has('parser.default')) {
            $item_BP = Item_BP::all(); 
            $item_F = Item_Fast::all();  
            $item_S = Item_Steam::all();
            foreach($item_BP as $item){
                if(!isset($items[$item->market_hash_name])){
                    $items[$item->market_hash_name] = [$item->price];
                } else {
                    $items[$item->market_hash_name][] = $item->price;
                }
            }
            foreach($item_F as $item){
                if(!isset($items[$item->market_hash_name])){
                    $items[$item->market_hash_name] = [$item->price];
                } else {
                    $items[$item->market_hash_name][] = $item->price;
                }
            }
            foreach($item_S as $item){
                if($item->price < 20){
                    $items[$item->market_hash_name] = [$item->price];
                } else if(!isset($items[$item->market_hash_name])){
                    $items[$item->market_hash_name] = [$item->price];
                } else {
                    $items[$item->market_hash_name][] = $item->price;
                }
            }
            foreach($items as $key => $item){
                $sp = 0; $c = 0;
                foreach($item as $price){
                    $sp += $price;
                    $c++;
                }
                $items[$key] = $sp/$c;
            }
            $items = json_encode($items);
            Cache::put('parser.default', $items, 1 * 60 * 60);
        } else {
            $items = Cache::get('parser.default');
        }
        return $items;
    }
    private function getNSouvenir(){
        $items = [];
        if(!Cache::has('parser.no_souvenir')) {
            $item_BP = Item_BP::all(); 
            $item_F = Item_Fast::all();  
            $item_S = Item_Steam::all();
            foreach($item_BP as $item){
                if((strrpos(strtolower(' '.$item->market_hash_name.' '), "souvenir") === false)&&(strrpos(strtolower(' '.$item->market_hash_name.' '), "Сувенир")) === false){
                    if(!isset($items[$item->market_hash_name])){
                        $items[$item->market_hash_name] = [$item->price];
                    } else {
                        $items[$item->market_hash_name][] = $item->price;
                    }
                }
            }
            foreach($item_F as $item){
                if((strrpos(strtolower(' '.$item->market_hash_name.' '), "souvenir") === false)&&(strrpos(strtolower(' '.$item->market_hash_name.' '), "Сувенир")) === false){
                    if(!isset($items[$item->market_hash_name])){
                        $items[$item->market_hash_name] = [$item->price];
                    } else {
                        $items[$item->market_hash_name][] = $item->price;
                    }
                }
            }
            foreach($item_S as $item){
                if((strrpos(strtolower(' '.$item->market_hash_name.' '), "souvenir") === false)&&(strrpos(strtolower(' '.$item->market_hash_name.' '), "Сувенир")) === false){
                    if($item->price < 20){
                        $items[$item->market_hash_name] = [$item->price];
                    } else if(!isset($items[$item->market_hash_name])){
                        $items[$item->market_hash_name] = [$item->price];
                    } else {
                        $items[$item->market_hash_name][] = $item->price;
                    }
                }
            }
            foreach($items as $key => $item){
                $sp = 0; $c = 0;
                foreach($item as $price){
                    $sp += $price;
                    $c++;
                }
                $items[$key] = $sp/$c;
            }
            $items = json_encode($items);
            Cache::put('parser.no_souvenir', $items, 1 * 60 * 60);
        } else {
            $items = Cache::get('parser.no_souvenir');
        }
        return $items;
    }
    private function getNSteam(){
        $items = [];
        if(!Cache::has('parser.no_steam')) {
            $item_BP = Item_BP::all(); 
            $item_F = Item_Fast::all();  
            foreach($item_BP as $item){
                if(!isset($items[$item->market_hash_name])){
                    $items[$item->market_hash_name] = [$item->price];
                } else {
                    $items[$item->market_hash_name][] = $item->price;
                }
            }
            foreach($item_F as $item){
                if(!isset($items[$item->market_hash_name])){
                    $items[$item->market_hash_name] = [$item->price];
                } else {
                    $items[$item->market_hash_name][] = $item->price;
                }
            }
            foreach($items as $key => $item){
                $sp = 0; $c = 0;
                foreach($item as $price){
                    $sp += $price;
                    $c++;
                }
                $items[$key] = $sp/$c;
            }
            $items = json_encode($items);
            Cache::put('parser.no_steam', $items, 1 * 60 * 60);
        } else {
            $items = Cache::get('parser.no_steam');
        }
        return $items;
    }
    private function getSteam(){
        $items = [];
        if(!Cache::has('parser.steam')) {
            $item_S = Item_Steam::all();
            foreach($item_S as $item){
                $items[$item->market_hash_name] = $item->price;
            }
            $items = json_encode($items);
            Cache::put('parser.steam', $items, 1 * 60 * 60);
        } else {
            $items = Cache::get('parser.steam');
        }
        return $items;
    }
    public function parseSteam() {
        $data = $this->redis->lrange('parserSteam', 0, -1);
        foreach ($data as $strpage) {
            $json = json_decode($strpage);
            $this->redis->lrem('parserSteam', 0, $strpage);
            $sdata = $json->results_html;
            preg_match_all('%<a class="market_listing_row_link" href="(.+?)" id="resultlink.*?<span class="normal_price">(.+?) .+?</span>.+?<span class="sale_price">(.+?) .+?</span>.*?class="market_listing_item_name" style=".*?">(.+?)</span>%s', $sdata, $result, PREG_PATTERN_ORDER);
            for ($i = 0; $i < count($result[0]); $i++) {
                $steam_price_sale = trim($result[3][$i]);
                $steam_market_name = $result[4][$i];
                $steam_price_sale = str_replace(",", ".", $steam_price_sale);
                if($steam_price_sale == 0) $steam_price_sale = $this->getStemItemPrice($steam_market_name);
                $nitem = [ 'market_hash_name' => $steam_market_name, 'price' => $steam_price_sale ];
                $dbitem = Item_Steam::where('market_hash_name', $nitem['market_hash_name'])->first();
                if(is_null($dbitem)){
                    Item_Steam::create($nitem);
                } else {
                    $dbitem->price = $nitem['price'];
                    $dbitem->save();
                }
            }
            sleep(1);
        }
    } 
}