<?php

namespace App\Http\Controllers;

use App\Services\Item;
use Auth;
use Log;
use DB;
use App\Shop;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\CCentrifugo;
use App\Http\Controllers\Controller;

class ShopController extends Controller {
    // REDIS каналы
    const NEW_ITEMS_CHANNEL =       'items.to.sale';
    const GIVE_ITEMS_CHANNEL =      'items.to.give';
    const CHECK_ITEMS_CHANNEL =     'items.to.check';
    const STATUS_ITEMS_CHANNEL =    'items.to.status';
    const DECLINE_ITEMS_CHANNEL =   'shop.decline.list';
    const DEPOSIT_RESULT_CHANNEL =  'offers.deposit.result';

    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }
    public function index(){
        parent::setTitle('Магазин | ');
        $slimit = 0;
        $slimit_ = '';
        if(!Auth::guest()){
            $slimit = $this->user->slimit;
            while($slimit>1000){
                $slimit = round($slimit/1000,1);
                $slimit_ .='к';
            }
        }
        $slimit .= $slimit_;
        return view('pages.shop.shop', compact('slimit'));
    }
    public function updateShop(Request $request){
        if($request->get('id') == '*'){
            foreach (config('mod_shop.bots') as $bot_id => $bot) $this->redis->publish('s'.$bot_id.'_'.'updateShop', json_encode('data'));
        } else {
            $this->redis->publish('s'.$request->get('id').'_'.'updateShop', json_encode('data'));
        }
        return response()->json(['success' => true]);
    }
    public function clearShop(Request $request){
        if($request->get('id') == '*'){
            DB::table('shop')->truncate();
        } else {
            DB::table('shop')->where('bot_id', '=', $request->get('id'))->delete();
        }
        return response()->json(['success' => true]);
    }
    public function updateSTrade(Request $request){
        DB::table('shop_offers')->where('id', $request->get('id'))->update(['status' => $request->get('status')]);
        return response()->json(['success' => true]);
    }
    public function history()
    {
        parent::setTitle('История покупок | ');
        $items = Shop::where('buyer_id', $this->user->id)->orderBy('buy_at', 'desc')->get();
        $deposits = DB::table('deposits')->where('user_id', $this->user->id)->orderBy('date', 'desc')->get();
        $shop_offers = DB::table('shop_offers')->where('user_id', $this->user->id)->orderBy('date', 'desc')->get();
        return view('pages.shop.history', compact('items', 'deposits', 'shop_offers'));
    }
    public function admin()
    {
        parent::setTitle('История покупок | ');
        $items = [];
        $deposits = DB::table('deposits')->orderBy('date', 'desc')->get();
        $shop_offers = DB::table('shop_offers')->orderBy('date', 'desc')->get();
        return view('pages.shop.history', compact('items', 'deposits', 'shop_offers'));
    }
    
    public function sendItem($items,$botid)
    {
        $senditems = [];
        foreach ($items as $item) { $senditems[] = $item->inventoryId; }
        $value = [ 'appId' => config('mod_game.appid'), 'items' => $senditems, 'steamid' => $this->user->steamid64, 'accessToken' => $this->user->accessToken ];
        $this->redis->rpush('s'.$botid.'_'.self::GIVE_ITEMS_CHANNEL, json_encode($value));
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
    public static function checkSales(){
        $tsales = Shop::where('sale', 1)->where('buy_at', '>=', Carbon::today())->count();
        if(($tsales - 8) < config('mod_shop.sales_per_day')){
            $sales = Shop::where('status', Shop::ITEM_STATUS_FOR_SALE)->where('sale', 1)->count();
            $items = Shop::where('status', Shop::ITEM_STATUS_FOR_SALE)->where('status', 0)->where('sale', 0)->where('price', '>', 10)->where('price', '<', 200)->orderBy('id', 'asc')->limit(8-$sales)->get();
            foreach($items as $item){
                $item->sale = 1;
                $item->save();
                $sale = ['id' => $item->id, 'name' => $item->name, 'price' => round($item->price*0.9,2), 'oldprice' => $item->price, 'classid' => $item->classid, 'className' => Shop::getClassRarity($item->rarity) ];
                $returnValue = view('includes.sale', compact('sale'))->render();
                CCentrifugo::publish('addSale' , [ 'html' => $returnValue]);
            }
        }
        return;
    }
    public function buySale(Request $request){
        $id = $request->get('id');
        $sales = Shop::where('buyer_id', $this->user->id)->where('sale', 1)->where('buy_at', '>=', Carbon::today())->count();
        if($sales >= config('mod_shop.sales_per_day_user')) return response()->json(['success' => false, 'msg' => 'Максимум ' . config('mod_shop.sales_per_day_user') . ' предмета по скидке в сутки']);
        
        $item = Shop::find($id);
        if (!config('mod_shop.shop')) return response()->json(['success' => false, 'msg' => 'Магазин отключен']);
        if ($this->user->ban != 0) return response()->json(['success' => false, 'msg' => 'Вы забанены на сайте']);
        if (is_null($item)) return response()->json(['success' => false, 'msg' => 'Предмет не найден!']);
        if ($item->sale != 1) return response()->json(['success' => false, 'msg' => 'Скидка не действует на этот предмет']);
        $games = count($this->user->games());
        if(($games < config('mod_shop.games_need_count')) && config('mod_shop.games_need')) return response()->json(['success' => false, 'msg' => 'У вас должно быть больше '.config('mod_shop.games_need_count').' игр для совершения покупок']);
        $itemsum = round($item->price * 0.9,2);
        if (( $this->user->slimit < $itemsum ) && !$this->user->is_admin) return response()->json(['success' => false, 'msg' => 'Ваш лимит '.$this->user->slimit.'р.']);
        if(!User::mchange($this->user->id, -$itemsum)) return response()->json(['success' => false, 'msg' => 'У вас недостаточно средств!']);
        User::slchange($this->user->id, -$itemsum);
        $delitems = [$item->classid]; $senditems = []; 

        $item->status = Shop::ITEM_STATUS_SOLD;
        $item->buyer_id = $this->user->id;
        $item->buy_at = Carbon::now();
        $item->save();
        $senditems[] = $item;                                

        $this->sendItem($senditems, $item->bot_id);

        $returnValue = ['list' => $delitems, 'off' => false];
        CCentrifugo::publish('delShop' , $returnValue);
        CCentrifugo::publish('delSale' , ['id' => $item->id]);
        DB::table('deposits')->insertGetId([
            'user_id' => $this->user->id, 
            'date' => Carbon::now()->toDateTimeString(),
            'price' => $itemsum,
            'type' => Shop::D_BUY
        ]);
        self::checkSales();
        return response()->json(['success' => true, 'msg' => 'Предмет будет отправлен в течение 1 мин.']);
    }
    public function setItemStatus(Request $request)
    {
        $bot_id = $request->get('bot_id');
        $jsonItems = $this->redis->lrange('s'.$bot_id.'_'.self::STATUS_ITEMS_CHANNEL, 0, -1);
        foreach($jsonItems as $jsonItem){
            $this->redis->lrem('s'.$bot_id.'_'.self::STATUS_ITEMS_CHANNEL, 1, $jsonItem);
            $data = json_decode($jsonItem, true);
            $total_price = 0; $returnValue = []; $user_id = 0; $send_price = 0;
            foreach($data['items'] as $id) {
                $item = DB::table('shop')->where('inventoryId', $id)->where('bot_id', $bot_id)->first();
                if(!is_null($item)){
                    $status = $data['status'];
                    $item = Shop::find($item->id);
                    $item->status = $status;
                    $item->save();
                    if ($status == Shop::ITEM_STATUS_ERROR_TO_SEND || $status == Shop::ITEM_STATUS_RETURNED || $status == Shop::ITEM_STATUS_NOT_FOUND){
                        if($status != Shop::ITEM_STATUS_NOT_FOUND) self::makeNew($item);
                        if($item->sale == 1) {
                            $total_price += round($item->price * 0.9,2);
                        } else {
                            $total_price += $item->price;
                        }
                    } else {
                        if($item->sale == 1) {
                            $send_price += round($item->price * 0.9,2);
                        } else {
                            $send_price += $item->price;
                        }
                    }
                    $user_id = $item->buyer_id;
                }
            }
            $user = User::find($user_id);
            if(!is_null($user)){
                $total_price = round($total_price,2);
                if($send_price>0){
                    $this->_responseMessageToSite('Обмен отправлен | Сумма: ' . $send_price , $user->steamid64);
                } else {
                    $this->_responseMessageToSite('Средства возвращены | Сумма: ' . $total_price , $user->steamid64);
                    DB::table('deposits')->insertGetId([
                        'user_id' => $user->id, 
                        'date' => Carbon::now()->toDateTimeString(),
                        'price' => $total_price,
                        'type' => Shop::D_RETURN
                    ]);
                    User::mchange($user->id, $total_price);
                    User::slchange($user->id, $total_price);
                }
            }
        }
        return response()->json(['success' => false]);
    }
    private function makeNew($item)
    { 
        $returnValue = [];
        $newid = DB::table('shop')->insertGetId([
            'name' => $item->name,
            'classid' => $item->classid,
            'inventoryId' => $item->inventoryId,
            'rarity' => $item->rarity,
            'type' => $item->type,
            'bot_id' => $item->bot_id,
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
        CCentrifugo::publish('addShop' , $returnValue);
    }
    public function _parseItems($items)
    {
        $itemInfo = []; $total_price = 0; $i = 0;
        foreach ($items as $item) {
            $value = $item['classid'];
            if ($item['appid'] == config('mod_game.appid')) {
                if (!isset($itemInfo[$value])){
                    $info = new Item($item);
                    $itemInfo[$value] = $info;
                }
                if(Item::pchk($itemInfo[$value])){
                    if ($itemInfo[$value]->price <= config('mod_shop.dep_comission_from')) {
                        if ($itemInfo[$value]->price <= config('mod_shop.garbadge_from')) {
                            $itemInfo[$value]->price = $itemInfo[$value]->price/100*config('mod_shop.garbadge_%');
                        }
                        $itemInfo[$value]->price = $itemInfo[$value]->price * (1 - config('mod_shop.dep_comission_%')/100);
                    }
                    $total_price += $itemInfo[$value]->price;
                    $items[$i]['price'] = $itemInfo[$value]->price;
                    unset($items[$i]['appid']);
                    $i++;
                }
            }
        }
        return $total_price;
    }
    public function addItemsToSale(Request $request)
    {
        $bot_id = $request->get('bot_id');
        $jsonItems = $this->redis->lrange('s'.$bot_id.'_'.self::NEW_ITEMS_CHANNEL, 0, -1);
        foreach($jsonItems as $jsonItem){
            $returnValue = [];
            $items = json_decode($jsonItem, true);
            $total_price = $this->_parseItems($items);
            foreach($items as $item) {
                $info = new Item($item);
                if(Item::pchk($info)){
                    $item['steam_price'] = $info->price;
                    $item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
                    Shop::create($item);
                    $returnValue[] = [ $item['classid'], Shop::countItem($item['classid']), $item['name'], $item['price'], $item['classid'], $item['quality'], Shop::getClassRarity($item['rarity']), $item['rarity'] ];
                }
            }
            $returnValue = ['list' => $returnValue, 'off' => false];
            CCentrifugo::publish('addShop' , $returnValue);
            $this->redis->lrem('s'.$bot_id.'_'.self::NEW_ITEMS_CHANNEL, 1, $jsonItem);
        }        
        return response()->json(['success' => true]);
    }
    public function checkShop(Request $request){
        $bot_id = $request->get('bot_id');
        $items = DB::table('shop')->where('bot_id', '=', $bot_id)->get();
        $delitems = []; foreach ($items as $item){ $delitems[] = $item->classid; }
        $returnValue = ['list' => $delitems, 'off' => false];
        CCentrifugo::publish('delShop' , $returnValue);
        DB::table('shop')->where('bot_id', '=', $bot_id)->where('status', Shop::ITEM_STATUS_FOR_SALE)->delete();
        $jsonItems = $this->redis->lrange('s'.$bot_id.'_'.self::CHECK_ITEMS_CHANNEL, 0, -1);
        foreach($jsonItems as $jsonItem){
            $returnValue = [];
            $itemsToAdd = json_decode($jsonItem, true);
            $this->redis->lrem('s'.$bot_id.'_'.self::CHECK_ITEMS_CHANNEL, 1, $jsonItem);
            foreach($itemsToAdd as $item) {
                $info = new Item($item); 
                if (Item::pchk($info)) {
                    $item['steam_price'] = $info->price;
                    $item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
                    $item = Shop::create($item);
                    $returnValue[] = [ $item->id, Shop::countItem($item->classid), $item->name, $item->price, $item->classid, $item->quality, Shop::getClassRarity($item->rarity), $item->rarity ];
                }
            }
            $returnValue = ['list' => $returnValue, 'off' => false]; 
            CCentrifugo::publish('addShop' , $returnValue);
        }
        return response()->json(['success' => true]);
    }
    public function getcart(Request $request){
        if (\Cache::has('shop.user.' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
        \Cache::put('shop.user.' . $this->user->id, '', 5);
        
        if (!config('mod_shop.shop')) return response()->json(['success' => false, 'msg' => 'Магазин отключен']);
        if ($this->user->ban != 0) return response()->json(['success' => false, 'msg' => 'Вы забанены на сайте']);
        $classids = $request->get('classids'); $itemsum = 0; $fintems = [];
        if (is_null($classids)) return response()->json(['success' => false, 'msg' => 'Вы ничего не выбрали!']);
        if(count($classids) > config('mod_shop.select_limit')) return response()->json(['success' => false, 'msg' => 'Максимум '.config('mod_shop.select_limit').' предметов за раз.']);
        foreach ($classids as $classid){
            $items = DB::table('shop')->where('classid', $classid)->where('status', Shop::ITEM_STATUS_FOR_SALE)->get();
            foreach ($items as $item){ if (!in_array($item, $fintems)){ $fintems[] = $item; break; } }
        }
        if (count($fintems) == 0) return response()->json(['success' => false, 'msg' => 'Предметы не найдены']); 
        foreach ($fintems as $item) $itemsum += $item->price;
        
        $games = count($this->user->games());
        if(($games < config('mod_shop.games_need_count')) && config('mod_shop.games_need')) return response()->json(['success' => false, 'msg' => 'У вас должно быть больше '.config('mod_shop.games_need_count').' игр для совершения покупок']);
        
        if (( $this->user->slimit < $itemsum ) && !$this->user->is_admin) return response()->json(['success' => false, 'msg' => 'Ваш лимит '.$this->user->slimit.'р.']);
        if(!User::mchange($this->user->id, -$itemsum)) return response()->json(['success' => false, 'msg' => 'У вас недостаточно средств!']);
        User::slchange($this->user->id, -$itemsum);
        $delitems = []; $botitems = [];
        foreach ($fintems as $i){
            $botitems[$i->bot_id][] = $i;
        }
        foreach ($botitems as $bot_id => $bitems){
            $senditems = [];
            foreach ($bitems as $i){
                $item = Shop::find($i->id);
                $delitems[] = $item->classid;
                $item->status = Shop::ITEM_STATUS_SOLD;
                $item->buyer_id = $this->user->id;
                $item->buy_at = Carbon::now();
                $item->save();
                $senditems[] = $item;                                
                if (count($senditems) == config('mod_shop.items_per_trade')){
                    $this->sendItem($senditems,$bot_id);
                    $senditems = [];
                }
            }
            if (count($senditems) > 0){
                $this->sendItem($senditems,$bot_id);
            }
        }
        $returnValue = ['list' => $delitems, 'off' => false];
        CCentrifugo::publish('delShop' , $returnValue);
        DB::table('deposits')->insertGetId([
            'user_id' => $this->user->id, 
            'date' => Carbon::now()->toDateTimeString(),
            'price' => $itemsum,
            'type' => Shop::D_BUY
        ]);
        return response()->json(['success' => true, 'msg' => 'Предметы будут отправлены в течение 1 мин.']);
    }
    
    
    // DEPOSIT
    
    
    public function deposit(){
        parent::setTitle('Депозит | ');
        return view('pages.shop.deposit');
    }
    public function itemlist(Request $request)
    {
        $items = Shop::where('bot_id', $request->get('bot_id'))->get(); $ids = [];
        foreach($items as $item) $ids[] = $item->inventoryId;
        return response()->json(['success' => true, 'items' => $ids]);
    }
    public function depositToCheck(Request $request)
    {
        $aoffers = DB::table('shop_offers')->where('bot_id', $request->get('bot_id'))->where('status', 0)->get();
        if(is_null($aoffers)) return response()->json(['success' => true, 'trades' => []]);
        if(!count($aoffers)) return response()->json(['success' => true, 'trades' => []]);
        $trades = []; foreach($aoffers as $offer) $trades[] = $offer->tradeid;
        if(count($trades) > 0) return response()->json(['success' => true, 'trades' => $trades]);
        return response()->json(['success' => false]);
    }
    private function checkoutdeposit($items, $trade, $total_price, $user)
    {
        $returnValue = [];
        foreach($items as $item) {
            $info = new Item($item);
            if(Item::pchk($info)){
                $item['steam_price'] = $info->price;
                $item['price'] = $item['steam_price']/100 * config('mod_shop.steam_price_%');
                Shop::create($item);
                $returnValue[] = [ $item['classid'], Shop::countItem($item['classid']), $item['name'], $item['price'], $item['classid'], $item['quality'], Shop::getClassRarity($item['rarity']), $item['rarity'] ];
            }
        }
        $returnValue = ['list' => $returnValue, 'off' => false];
        CCentrifugo::publish('addShop' , $returnValue);
        DB::table('shop_offers')->where('id', $trade->id)->update(['price' => $total_price, 'status' => 1]);
        $this->_responseMessageToSite('Депозит зачислен | Сумма: ' . $total_price , $user->steamid64); User::mchange($user->id, $total_price); User::slchange($user->id, $total_price);
        DB::table('deposits')->insert([ 'user_id' => $user->id, 'date' => Carbon::now()->toDateTimeString(), 'price' => $total_price, 'type' => Shop::D_DEPOSIT ]);
    }
    public function depositCheck(Request $request)
    {
        $bot_id = $request->get('bot_id');
        $data = $this->redis->lrange('s'.$bot_id.'_'.self::DEPOSIT_RESULT_CHANNEL, 0, -1);
        foreach ($data as $newTradeCheck) {
            $tradeCheck = json_decode($newTradeCheck, true);
            $trade = DB::table('shop_offers')->where('tradeid', $tradeCheck['id'])->first();
            if(!is_null($trade)){
                if($trade->status == 0){
                    $user = User::find($trade->user_id);
                    if($tradeCheck['status'] == 1){
                        $items = $tradeCheck['items'];
                        $total_price = round($this->_parseItems($items),2);
                        $diff = abs($trade->price - $total_price);
                        if(($total_price > 0) && ($trade->price/10 > $diff)){
                            self::checkoutdeposit($items, $trade, $total_price, $user);
                        } else {
                            if($trade->ecount < 10){
                                DB::table('shop_offers')->where('id', $trade->id)->update(['ecount' => $trade->ecount + 1]);
                                $this->_responseMessageToSite('Ваш депозит повторно обрабатывается', $user->steamid64);
                            } else {
                                self::checkoutdeposit($items, $trade, $total_price, $user);
                            }
                        }
                        $this->redis->lrem('s'.$bot_id.'_'.self::DEPOSIT_RESULT_CHANNEL, 1, $newTradeCheck);
                    }
                    if($tradeCheck['status'] == 2){
                        $this->redis->lrem('s'.$bot_id.'_'.self::DEPOSIT_RESULT_CHANNEL, 1, $newTradeCheck);
                    }
                    if($tradeCheck['status'] == 0){
                        $this->_responseMessageToSite('Обмен #' . $trade->tradeid . ' не действителен', $user->steamid64);
                        DB::table('shop_offers')->where('id', $trade->id)->update(['status' => 2]);
                        $this->redis->lrem('s'.$bot_id.'_'.self::DEPOSIT_RESULT_CHANNEL, 1, $newTradeCheck);
                    }
                }
            }
        }
        return response()->json(['success' => true]);
    }
    public function sellitems(Request $request){
        if (\Cache::has('shop.user.' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
        \Cache::put('shop.user.' . $this->user->id, '', 5);
        if (!config('mod_shop.shop')) return response()->json(['success' => false, 'msg' => 'Магазин отключен']);
        $aoffer = DB::table('shop_offers')->where('user_id', $this->user->id)->where('status', 0)->first();
        if(!is_null($aoffer)) return response()->json(['success' => false, 'msg' => 'У вас уже есть неподтвержденный обмен']);
        $classids = $request->get('classids');
        if($classids == '') return response()->json(['success' => false, 'msg' => 'Вы не выбрали предметов']);
        $items = explode(',', $classids);
        if((count($items)-1) > config('mod_shop.select_limit')) return response()->json(['success' => false, 'msg' => 'Максимум '.config('mod_shop.select_limit').' предметов за раз.']);
        $value = [
            'items' => $classids,
            'steamid' => $this->user->steamid64,
            'price' => round($request->get('price'),2),
            'accessToken' => $this->user->accessToken
        ];
        $shop_id = Shop::selectBot();
        $out = GameController::curl( config('app.url') . '/sendTrade/'.$shop_id.'/?data='.json_encode($value).'&secretKey=' . config('app.secretKey'));
        $out = json_decode($out, true);
        if(isset($out['success']) && $out['success'] == true) {
            $id = DB::table('shop_offers')->insertGetId([
                'user_id' => $this->user->id, 
                'date' => Carbon::now()->toDateTimeString(),
                'bot_id' => $shop_id,
                'tradeid' => $out['tradeid'],
                'price' => $request->get('price'),
                'status' => 0
            ]);
            return response()->json(['success' => true, 'msg' => $out['code'], 'tradeid' => $out['tradeid']]);
        } else {
            $msg = 'Ошибка подключения';
            if(isset($out['error'])) $msg = $out['error'];
            return response()->json(['success' => false, 'msg' => $msg]);
        }
    }
    public function myinventory(Request $request)
    {
        $success = true; $returnValue = [];
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
        if (\Cache::has('shop.user_inv_' . $this->user->id)) return response()->json(['success' => false, 'msg' => 'Подождите...']);
        \Cache::put('shop.user_inv_' . $this->user->id, '', 30);
        $returnValue = self::updatemyinventory($this->user->steamid64);
        $success = false;
        if($returnValue['success']){
            $success = $returnValue['success'];
            $returnValue = $returnValue['list'];
            \Cache::put('shop_inv_' . $this->user->steamid64, $returnValue, 12 * 60 * 60);
        }
        return response()->json(['success' => $success]);
    }
    private function updatemyinventory($userid)
    {
        $jsonInventory = GameController::curl('http://steamcommunity.com/inventory/' . $userid . '/730/2?l=russian&count=1000');
        $items = json_decode($jsonInventory, true);
        $descriptions = [];$myItems = [];$returnValue = [];$success = true;
        if(isset($items['assets']) && isset($items['descriptions']) && isset($items['success'])){
            if ($items['success'] == 1) {
                foreach ($items['descriptions'] as $id => $value) {
                    $class_instance = $value['classid'].'_'.$value['instanceid'];
                    $descriptions[$class_instance] = $value;
                }
                foreach ($items['assets'] as $id => $value) {
                    if(!isset($myItems[$value['classid']])){
                        $class_instance = $value['classid'].'_'.$value['instanceid'];
                        $item = $descriptions[$class_instance];
                        $info = new Item($item);
                        if(Item::pchk($info)){
                            $item['price'] = $info->price;
                            if ($item['price'] <= config('mod_shop.dep_comission_from')) {
                                if ($item['price'] <= config('mod_shop.garbadge_from')) {
                                    $item['price'] = $item['price']/100*config('mod_shop.garbadge_%');
                                }
                                $item['price'] = round($item['price'] * (1 - config('mod_shop.dep_comission_%')/100) * 100)/100;
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
                            $myItems[$value['classid']] = [
                                $value['classid'], 
                                1,
                                $name, 
                                $item['price'], 
                                $value['classid'], 
                                $quality, 
                                Shop::getClassRarity($rarity), 
                                $rarity,
                                [$value['assetid']]
                            ];
                        }
                    } else {
                        $myItems[$value['classid']][1] += 1;
                        $myItems[$value['classid']][8][] = $value['assetid'];
                    }
                }
            } else {
                $success = false;
            }
        } else {
            $success = false;
        }
        foreach($myItems as $key => $mi){
            $returnValue[] = $mi;
        }
        return ['list' => $returnValue, 'success' => $success];
    }
}