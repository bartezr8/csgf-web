<?php

namespace App\Http\Controllers;

use App\Item;
use App\Services\SteamItem;
use App\Shop;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ShopController extends Controller {
	// REDIS каналы
    const NEW_ITEMS_CHANNEL = 				'items.to.sale';
    const GIVE_ITEMS_CHANNEL = 				'items.to.give';
	const CHECK_ITEMS_CHANNEL = 			'items.to.check';

    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
	
    public function index(){
        parent::setTitle('Магазин | ');
		$betssum = \DB::table('bets')->where('user_id', $this->user->id)->orderBy('id')->sum('price');
		$betssum = round($betssum / 1000 , 2); 
		if($betssum > 50) $betssum = 50.00;
        return view('pages.shop.shop', compact('betssum'));
    }
    
    public function deposit(){
        parent::setTitle('Депозит | ');
        return view('pages.shop.deposit');
    }

    public function history()
	{
        parent::setTitle('История покупок | ');
		$items = Shop::where('buyer_id', $this->user->id)->orderBy('buy_at', 'desc')->get();
		if($this->user->is_admin == 1){
			$deposits = \DB::table('deposits')->orderBy('date', 'desc')->get();
		} else {
			$deposits = \DB::table('deposits')->where('user_id', $this->user->id)->orderBy('date', 'desc')->get();
		}
        return view('pages.shop.history', compact('items', 'deposits'));
    }
	
    public function shop()
	{
		$returnValue = [];
        $items = Shop::where('status', Shop::ITEM_STATUS_FOR_SALE)
			->groupBy('classid')
            ->orderBy('price', 'desc')
			->orderBy('name')
            ->get();
		foreach ($items as $item) {
			$returnValue[] = [
				$item->id, 
				Shop::countItem($item->classid), 
				$item->name, 
				$item->price, 
				$item->classid, 
				$item->quality, 
				Shop::getClassRarity($item->rarity), 
				$item->rarity
			];
		}
		return response()->json(['list' => $returnValue, 'off' => false]);
    }
	
    public function setItemStatus(Request $request)
	{
		$item = \DB::table('shop')->where('inventoryId', $request->get('id'))->first();
        if(!is_null($item)){
			$item = Shop::find($item->id);
            $item->status = $request->get('status');
            $item->save();
			$returnValue = [];
			if ($request->get('status') == Shop::ITEM_STATUS_ERROR_TO_SEND || $request->get('status') == Shop::ITEM_STATUS_RETURNED || $request->get('status') == Shop::ITEM_STATUS_NOT_FOUND){
				if($request->get('status') != Shop::ITEM_STATUS_NOT_FOUND){
					$newid = \DB::table('shop')->insertGetId([
						'name' => $item->name,
						'classid' => $item->classid, 
						'inventoryId' => $item->inventoryId, 
						'rarity' => $item->rarity, 
						'type' => $item->type, 
						'quality' => $item->quality, 
						'status' => Shop::ITEM_STATUS_FOR_SALE, 
						'steam_price' => $item->steam_price, 
						'price' => $item->price
					]);
					$newitem = Shop::find($newid);
					$returnValue[] = [
						$newitem->id, 
						Shop::countItem($newitem->classid), 
						$newitem->name, 
						$newitem->price, 
						$newitem->classid, 
						$newitem->quality, 
						Shop::getClassRarity($newitem->rarity), 
						$newitem->rarity
					];
					$returnValue = ['list' => $returnValue, 'off' => false];
					$this->redis->publish('addShop', json_encode($returnValue));
				}
				$user = User::find($item->buyer_id);
				$user->money = $user->money + $item->price;
				$user->save();
				if($request->get('status') == Shop::ITEM_STATUS_ERROR_TO_SEND) $this->_responseMessageToSite('Ошибка отправки ' . $item->name . ' - возвращаем: ' . $item->price . 'р.' , $user->steamid64);
				if($request->get('status') == Shop::ITEM_STATUS_NOT_FOUND) $this->_responseMessageToSite($item->name . ' не найден - возвращаем: ' . $item->price . 'р.' , $user->steamid64);
				if($request->get('status') == Shop::ITEM_STATUS_RETURNED) $this->_responseMessageToSite($item->name . ' не принят - возвращаем: ' . $item->price . 'р.' , $user->steamid64);
			}
            return $item;
        }
        return response()->json(['success' => false]);
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
    private function get_real_price($price, $mhn){
		if ($price < 5){
            $tprice = self::curl('http://steamcommunity.com/market/priceoverview/?currency=5&country=ru&appid='.config('mod_game.appid').'&market_hash_name=' . urlencode($mhn) . '&format=json');
            $tprice = json_decode($tprice);
            if (isset($tprice->success)){
                $lowest = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->lowest_price)));
                $median = floatval(str_ireplace(array(','),'.',str_ireplace(array('pуб.'),'',$tprice->median_price)));
                if($lowest<$median) $price = $lowest; else $price = $median;
            }
            sleep(1);
		}
		return $price;
	}
    public function _parseItems($items)
	{
        $itemInfo = [];
        $total_price = 0;
        $i = 0;

        foreach ($items as $item) {
            $value = $item['classid'];
            if ($item['appid'] == config('mod_game.appid')) {
                $dbItemInfo = Item::where('market_hash_name', $item['market_hash_name'])->first();
                if (is_null($dbItemInfo)) {
                    if (!isset($itemInfo[$value])) $itemInfo[$value] = new SteamItem($item);
                    if (!$itemInfo[$value]->price){
                        $price = true;
                        break;
                    } else {
                        $itemInfo[$value]->price = self::get_real_price($itemInfo[$value]->price, $item['market_hash_name']);
                        $dbItemInfo = Item::create((array)$itemInfo[$item['classid']]);
                    }
                } else {
                    if ($dbItemInfo->updated_at->getTimestamp() < Carbon::now()->subHours(24)->getTimestamp()) {
                        $si = new SteamItem($item);
                        if ($si->price){
                            $dbItemInfo->price = self::get_real_price($si->price, $item['market_hash_name']);
                            $dbItemInfo->save();
                        }
                    }
                }
				$itemInfo[$value] = $dbItemInfo;
                if ($itemInfo[$value]->price <= 0) $itemInfo[$value]->price = 0.1;
				if ($itemInfo[$value]->price <= config('mod_shop.comission_on_<')) {
					if ($itemInfo[$value]->price <= 10) {
						$itemInfo[$value]->price = $itemInfo[$value]->price/2;
					}
					$itemInfo[$value]->price = $itemInfo[$value]->price * (1 - config('mod_shop.comission_on_%')/100);
				}
				$total_price += $itemInfo[$value]->price;
				$items[$i]['price'] = $itemInfo[$value]->price;
				unset($items[$i]['appid']);
				$i++;
			}
        }
        return $total_price;
    }
   public function myinventory(Request $request)
    {
		$success = true;
		$returnValue = [];

		if(!\Cache::has('shop_inv_' . $this->user->steamid64)) {
			$returnValue = self::updatemyinventory($this->user->steamid64);
			$success = $returnValue['success'];
			$returnValue = $returnValue['list'];
			\Cache::put('shop_inv_' . $this->user->steamid64, $returnValue, 12 * 60 * 60);
		} else {
			$returnValue = \Cache::get('shop_inv_' . $this->user->steamid64);
		}
		$list = $returnValue;
		return response()->json(['list' => $list, 'success' => $success]);
    }
	public function inv_update(Request $request){
		$returnValue = self::updatemyinventory($this->user->steamid64);
		if($returnValue['success']){
			$success = $returnValue['success'];
			$returnValue = $returnValue['list'];
			\Cache::put('shop_inv_' . $this->user->steamid64, $returnValue, 12 * 60 * 60);
		}
		return response()->json(['success' => $success]);
	}
	
    private function updatemyinventory($userid)
    {
		$success = true;
		$returnValue = [];

		$jsonInventory = file_get_contents('http://steamcommunity.com/profiles/' . $userid . '/inventory/json/730/2?l=russian');
		$items = json_decode($jsonInventory, true);
		if ($items['success']) {
			foreach ($items['rgInventory'] as $id => $value) {
                $class_instance = $value['classid'].'_'.$value['instanceid'];
                $item = $items['rgDescriptions'][$class_instance];
                $info = Item::where('market_hash_name', $item['market_hash_name'])->first();
                if (is_null($info)){
                    $info = new SteamItem($item);
                    if (!$info->price){
                        $info->price = 0;
                    } else {
                        $info->price = self::get_real_price($info->price, $item['market_hash_name']);
                        $info = Item::create((array)$info);
                    }
                    //$info = (object)['price'=>0];
                }
				if($info->price){
					
					$item['price'] = $info->price;
					
					if ($item['price'] <= config('mod_shop.comission_on_<')) {
						if ($item['price'] <= 10) {
							$item['price'] = $item['price']/2;
						}
						$item['price'] = round($item['price'] * (1 - config('mod_shop.comission_on_%')/100) * 100)/100;
					}
						
					if(preg_match('/\(([^()]*)\)/', $item['market_name'], $nameval, PREG_OFFSET_CAPTURE)){
						$name = trim(substr( $item['market_name'] , 0 , $nameval[0][1] ));
						$quality = $nameval[1][0];
					} else {
						$name = $item['market_name'];
						$quality = NULL;
					}
					$rarity = preg_split('/,/', $item['type'], PREG_SPLIT_OFFSET_CAPTURE);
					$rarity = trim($rarity[count($rarity) - 1]);
					$returnValue[] = [
						$value['id'], 
						$name, 
						$item['price'], 
						$value['classid'], 
						$quality, 
						Shop::getClassRarity($rarity), 
						$rarity
					];
				}
			}
		} else {
			$success = false;
		}

		return ['list' => $returnValue, 'success' => $success];
    }
    public function addItemsToSale()
	{
        $jsonItems = $this->redis->lrange(self::NEW_ITEMS_CHANNEL, 0, -1);
        foreach($jsonItems as $jsonItem){
			$returnValue = [];
			$userid = config('mod_game.bot_steamid');
            $items = json_decode($jsonItem, true);
			$total_price = $this->_parseItems($items);
            foreach($items as $item) {
				$userid = $item['depositorid'];
                $info = Item::where('market_hash_name', $item['market_hash_name'])->first();
                if (is_null($info)) {
                    $info = new SteamItem($item);
                    if (is_null($info->price)) {
                        $info->price = self::get_real_price($info->price);
                        $info = Item::create((array)$info);
                    } else {
                        $info->price = 0;
                    }
                } 
                if($info->price > 0){
                    $item['steam_price'] = $info->price;
                    $item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
                    Shop::create($item);
                }
				$returnValue[] = [
					$item['classid'], 
					Shop::countItem($item['classid']), 
					$item['name'], 
					$item['price'], 
					$item['classid'], 
					$item['quality'], 
					Shop::getClassRarity($item['rarity']), 
					$item['rarity']
				];
			}
			$returnValue = ['list' => $returnValue, 'off' => false];
			$this->redis->publish('addShop', json_encode($returnValue));
            $this->redis->lrem(self::NEW_ITEMS_CHANNEL, 1, $jsonItem);
			$this->_responseMessageToSite('Депозит зачислен | Сумма: ' . $total_price , $userid);
			$user = User::where('steamid64', $userid)->first();
			if(!is_null($user)){
				if($userid != config('mod_game.bot_steamid')){
					$user->money = $user->money + $total_price;
					$user->save();
					\DB::table('deposits')->insertGetId([
						'user_id' => $user->id, 
						'date' => Carbon::now()->toDateTimeString(),
						'price' => $total_price,
						'type' => 0
					]);
				}
			}
        }		
        return response()->json(['success' => true]);
    }
	
    public function checkShop(){
		$items = \DB::table('shop')->get();
		$delitems = [];
		foreach ($items as $item){
			$delitems[] = $item->classid;
		}
		$returnValue = ['list' => $delitems, 'off' => false];
		$this->redis->publish('delShop', json_encode($returnValue));
		\DB::table('shop')->truncate();
        $jsonItems = $this->redis->lrange(self::CHECK_ITEMS_CHANNEL, 0, -1);
        foreach($jsonItems as $jsonItem){
			$returnValue = [];
            $itemsToAdd = json_decode($jsonItem, true);
            $this->redis->lrem(self::CHECK_ITEMS_CHANNEL, 1, $jsonItem);
			foreach($itemsToAdd as $item) {
				$dbItemInfo = Item::where('market_hash_name', $item['market_hash_name'])->first();
				if (is_null($dbItemInfo)) {
					$itemInfo = new SteamItem($item);
					$item['steam_price'] = $itemInfo->price;
					$item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
					Shop::create($item);
				}else{
					$item['steam_price'] = $dbItemInfo->price;
					$item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
					Shop::create($item);
				}
				$item = Shop::where('inventoryId', $item['inventoryId'])->first();
				$returnValue[] = [
					$item->id, 
					Shop::countItem($item->classid), 
					$item->name, 
					$item->price, 
					$item->classid, 
					$item->quality, 
					Shop::getClassRarity($item->rarity), 
					$item->rarity
				];
				$returnValue = ['list' => $returnValue, 'off' => false];
				$this->redis->publish('addShop', json_encode($returnValue));
				$returnValue = [];
			}
		}
        return response()->json(['success' => true]);
    }
	
    public function sendItem($items){
		$senditems = [];
        foreach ($items as $item) {
            $senditems[] = $item->inventoryId;
        }
		
        $value = [
            'items' => $senditems,
            'steamid' => $this->user->steamid64,
            'accessToken' => $this->user->accessToken
        ];

        $this->redis->rpush(self::GIVE_ITEMS_CHANNEL, json_encode($value));
    }
	
    public function checkOffers(){
		if (\Cache::has('shop.user.' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
		\Cache::put('shop.user.' . $this->user->id, '', 5);
		$aoffer = \DB::table('shop_offers')->where('user_id', $this->user->id)->where('status', 0)->first();
		if(is_null($aoffer)){
			return response()->json(['success' => false, 'msg' => 'У вас нет обменов к проверке']);
		} else {
			$value = [
				'tradeid' => $aoffer->tradeid
			];
			$jsonInventory = file_get_contents('https://api.steampowered.com/IEconService/GetTradeOffer/v1/?key=' . env('STEAM_APIKEY','') . '&format=json&tradeofferid=' . $aoffer->tradeid . '&language=russian');
			$steam = json_decode($jsonInventory, true);
			$out = $steam['response']['offer'];
			if(isset($out['trade_offer_state']) && isset($out['items_to_receive'])) {
				if($out['trade_offer_state'] == 3){
					$items = \DB::table('shop')->where('status', SHOP::ITEM_STATUS_FOR_SALE)->select('shop.inventoryId')->get();
					$iids = [];
					foreach ($items as $i){
						$iids[] = $i->inventoryId;
					}
					$cids = [];
                    if(count($out['items_to_receive']) == 0) return response()->json(['success' => false, 'msg' => 'Ошибка, не можем загрузить обмен']);
					foreach ($out['items_to_receive'] as $i){
						$cids[] = $i['classid'];
					}
					$botinv = file_get_contents('http://steamcommunity.com/profiles/' . config('mod_game.shop_steamid64') . '/inventory/json/730/2?l=russian');
					$botinv = json_decode($botinv, true);
					if(isset($botinv['rgInventory']) && isset($botinv['rgDescriptions'])){
						$returnValue = [];
						$items_for_parse = []; 
                        if(count($botinv['rgInventory']) == 0) return response()->json(['success' => false, 'msg' => 'Ошибка, не можем загрузить инвентарь']);
						foreach($botinv['rgInventory'] as $id => $value){
							if(count($cids) == 0) break;
							if(!in_array($id, $iids) && in_array($value['classid'], $cids)){
								$iids[] = $id;
								unset($cids[array_search($value['classid'], $cids)]);
								$class_instance = $value['classid'].'_'.$value['instanceid'];
								
								$item = $botinv['rgDescriptions'][$class_instance];
								$item['inventoryId'] = $id;
								$info = Item::where('market_hash_name', $item['market_hash_name'])->first();
								if (is_null($info)) {
									$info = new SteamItem($item);
									if (is_null($info->price)) {
										$info->price = self::get_real_price($info->price);
										$info = Item::create((array)$info);
									} else {
                                        $info->price = 0;
                                    }
								} 
								if($info->price){
									$item['steam_price'] = $info->price;
									$item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
									Shop::create($item);
									
									if(preg_match('/\(([^()]*)\)/', $item['market_name'], $nameval, PREG_OFFSET_CAPTURE)){
										$name = trim(substr( $item['market_name'] , 0 , $nameval[0][1] ));
										$quality = $nameval[1][0];
									} else {
										$name = $item['market_name'];
										$quality = NULL;
									}
									$rarity = preg_split('/,/', $item['type'], PREG_SPLIT_OFFSET_CAPTURE);
									$rarity = trim($rarity[count($rarity) - 1]);
									$item['quality'] = $quality;
									$item['rarity'] = $rarity;
									$returnValue[] = [
										$item['classid'], 
										Shop::countItem($item['classid']), 
										$item['name'], 
										$item['price'], 
										$item['classid'], 
										$item['quality'], 
										Shop::getClassRarity($item['rarity']), 
										$item['rarity']
									];
									$items_for_parse[] = [
										'classid' => $value['classid'],
										'appid' => $item['appid'],
										'name' => $item['name'],
										'rarity' => $rarity,
										'type' => $item['type'],
										'market_hash_name' => $item['market_hash_name']
									];
								}
							}
						}
                        if(count($items_for_parse) == 0){
                            return response()->json(['success' => false, 'msg' => 'Ошибка при оцентке вещей!']);
                        }
						$returnValue = ['list' => $returnValue, 'off' => false];
						$this->redis->publish('addShop', json_encode($returnValue));
						
						$user = User::find($aoffer->user_id);
						$total_price = $this->_parseItems($items_for_parse);
						$user->money += $total_price;
						$user->save();
						\DB::table('deposits')->insertGetId([
							'user_id' => $user->id, 
							'date' => Carbon::now()->toDateTimeString(),
							'price' => $total_price,
							'type' => 0
						]);
						\DB::table('shop_offers')->where('id', $aoffer->id)->update(['price' => $total_price, 'status' => 1]);
						return response()->json(['success' => true, 'msg' => 'Депозит зачислен | Сумма: ' . $total_price, 'active' => false]);
					} else {
						return response()->json(['success' => false, 'msg' => 'Ошибка, не можем загрузить инвентарь']);
					}
				} else if($out['trade_offer_state'] == 2){
					return response()->json(['success' => true, 'msg' => 'Обмен активен', 'active' => true, 'tradeid' => $aoffer->tradeid]);
				} else {
					\DB::table('shop_offers')->where('id', $aoffer->id)->delete();
					return response()->json(['success' => false, 'msg' => 'Обмен закрыт', 'active' => false]);
				}
			} else {
				$msg = 'Ошибка, стим недоступен';
                
				if(isset($out['error'])) $msg = $out['error'];
				return response()->json(['success' => false, 'msg' => $msg]);
			}
		}
    }
	public function sellitems(Request $request){
		if (\Cache::has('shop.user.' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
		\Cache::put('shop.user.' . $this->user->id, '', 5);
		if (config('mod_shop.shop')){
			$aoffer = \DB::table('shop_offers')->where('user_id', $this->user->id)->where('status', 0)->first();
			if(is_null($aoffer)){
				$classids = $request->get('classids');
				if($classids != ""){
					$str = '';
					foreach($classids as $classid){
						if($str == ''){
							$str = $classid;
						} else {
							$str += ',' . $classid;
						}
					}
					$value = [
						'items' => $classids,
						'steamid' => $this->user->steamid64,
						'accessToken' => $this->user->accessToken,
					];

					$out = self::curl('http://' . config('mod_shop.shop_strade_ip') . ':' . config('mod_shop.shop_strade_port') . '/sendTrade/?data='.json_encode($value).'&SHOP_SECRET=' . config('app.secretKey'));
					$out = json_decode($out, true);
					if($out['success'] == true) {
						$id = \DB::table('shop_offers')->insertGetId([
							'user_id' => $this->user->id, 
							'date' => Carbon::now()->toDateTimeString(),
							'tradeid' => $out['tradeid'],
							'status' => 0
						]);
						return response()->json(['success' => true, 'msg' => 'Обмен отправлен. Код: ' . $out['code'], 'tradeid' => $out['tradeid']]);
					} else {
						$msg = 'Ошибка';
						if(isset($out['error'])) $msg = $out['error'];
						return response()->json(['success' => false, 'msg' => $msg]);
					}
				} else {
					return response()->json(['success' => false, 'msg' => 'ВЫ не выбрали предметов']);
				}
			} else {
				return response()->json(['success' => false, 'msg' => 'У вас уже есть неподтвержденный обмен']);
			}
		} else {
			return response()->json(['success' => false, 'msg' => 'Магазин отключен']);
		}
	}
    public function getcart(Request $request){
		if (\Cache::has('shop.user.' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
		\Cache::put('shop.user.' . $this->user->id, '', 5);
		if (config('mod_shop.shop')){
			if ($this->user->ban == 0){
				$classids = $request->get('classids');
				if (!is_null($classids)){
					$fintems = [];
					foreach ($classids as $classid){
						$items = \DB::table('shop')->where('classid', $classid)->where('status', Shop::ITEM_STATUS_FOR_SALE)->get();
						foreach ($items as $item){
							if (!in_array($item->id, $fintems)){
								$fintems[] = $item->id;
								break;
							}
						}
					}
					$itemsum = 0;
					$takesum = 0;
					if (count($fintems) == 0) {
						return response()->json(['success' => false, 'msg' => 'Предметы не найдены']);
					}
					foreach ($fintems as $i){
						$item = Shop::find($i);
						$itemsum += $item->price;
						if ($item->price >= 5)$takesum+=$item->price;
					}
					$games = count(\DB::table('games')
						->join('bets', 'games.id', '=', 'bets.game_id')
						->where('bets.user_id', $this->user->id)
						->groupBy('bets.game_id')
						->select('bets.id')->get());
					if($itemsum <= $this->user->money){
						if(($games > config('mod_shop.games_need_count')) || config('mod_shop.games_need')){
							$bsum = \DB::table('shop')->where('buyer_id', $this->user->id)->where('buy_at', '>=', Carbon::now()->subDay())->where('price', '>=', 5)->sum('price');
							$dsum = \DB::table('deposits')->where('user_id', $this->user->id)->where('date', '>=', Carbon::now()->subDay())->where('type', 0)->sum('price');
							$fksum = \DB::table('freekassa_payments')->where('account', $this->user->id)->where('status', 1)->where('dateComplete', '>=', Carbon::now()->subDay())->sum('AMOUNT');
							$gdsum = \DB::table('gdonate_payments')->where('account', $this->user->id)->where('status', 1)->where('dateComplete', '>=', Carbon::now()->subDay())->sum('sum');
							$betssum = \DB::table('bets')->where('user_id', $this->user->id)->orderBy('id')->sum('price');
							$betssum = round($betssum / 1000 , 2); 
							if($betssum > 50) $betssum = 50.00;
							$canget = ($betssum * config('mod_shop.max_daily_sum')) + $dsum + $gdsum + $fksum;
							if ( $bsum + $takesum <= $canget || !config('mod_shop.max_daily') || $this->user->is_admin){
								$this->user->money = $this->user->money - $itemsum;
								$this->user->save();
								$senditems = [];
								$j = 0;
								$delitems = [];
								foreach ($fintems as $i){
									$thisitem = Shop::find($i);
									$delitems[] = $thisitem->classid;
									$thisitem->status = Shop::ITEM_STATUS_SOLD;
									$thisitem->buyer_id = $this->user->id;
									$thisitem->buy_at = Carbon::now();
									$thisitem->save();
									$senditems[] = $thisitem;								
									if (count($senditems) == config('mod_shop.items_per_trade')){
										$this->sendItem($senditems);
										$j = 0;
										$senditems = [];
									}
								}
								if (count($senditems) > 0){
									$this->sendItem($senditems);
								}
								$returnValue = ['list' => $delitems, 'off' => false];
								$this->redis->publish('delShop', json_encode($returnValue));
								\DB::table('deposits')->insertGetId([
									'user_id' => $this->user->id, 
									'date' => Carbon::now()->toDateTimeString(),
									'price' => $itemsum,
									'type' => 1
								]);
								return response()->json(['success' => true, 'msg' => 'Предметы будут отправлены в течение 1 мин.']);
							} else {
								$left = $canget - $bsum;
								return response()->json(['success' => false, 'msg' => 'У вас осталось '.$left.'/'.$canget.'р. в день.']);
							}
						} else {
							return response()->json(['success' => false, 'msg' => 'У вас должно быть больше '.config('mod_shop.games_need_count').' игр для покупки в магазине']);
						}
					} else {
						return response()->json(['success' => false, 'msg' => 'У вас недостаточно средств!']);
					}
				} else {
					return response()->json(['success' => false, 'msg' => 'Вы ничего не выбрали!']);
				}
			} else {
				return response()->json(['success' => false, 'msg' => 'Вы забанены на сайте']);
			}
		} else {
			return response()->json(['success' => false, 'msg' => 'Магазин отключен']);
		}
	}
}