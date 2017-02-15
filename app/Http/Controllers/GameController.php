<?php

namespace App\Http\Controllers;
use DB;
use Log;
use Auth;
use App\Bet;
use App\Bot_bet;
use App\Game;
use App\Services\Item;
use App\Ticket;
use App\User;
use App\Shop;
use Carbon\Carbon;
use LRedis;
use Illuminate\Http\Request;
use App\WinnerTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Cache;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CCentrifugo;

use Storage;

class GameController extends Controller
{
    const SEND_OFFERS_LIST = 'send.offers.list';
    const NEW_BET_CHANNEL = 'newDeposit';
    const BET_DECLINE_CHANNEL = 'depositDecline';
    const INFO_CHANNEL = 'msgChannel';
    const SHOW_WINNERS = 'show.winners';
    const LOG_CHANNEL = 'app_log';
    const QUEUE_CHANNEL = 'queue';
    const VIEW_BET_CHANNEL = 'view_bet';
    public $game;

    protected $lastTicket = 0;
    
    public function __construct()
    {
        $this->redis = LRedis::connection();
        parent::__construct();
        $this->game = $this->getLastGame();
        $this->lastTicket = $this->redis->get('last.ticket.' . $this->game->id);
        if(is_null($this->lastTicket)) $this->lastTicket = 0;
    }
    public function  __destruct()
    {
        $this->redis->disconnect();
    }
    public static function curl($url) {
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
    public function userinfo(Request $request)
    {
        $user = User::where('steamid64', $request->get('steamid'))->select('users.id','users.username','users.avatar','users.steamid64')->first();
        if(is_null($user)){
            $user = (object)[
                'steamid64' => config('mod_game.bonus_bot_steamid64')
            ];
        }
        return response()->json($user);
    }
    public function deposit()
    {
        $last = $this->redis->get('last.bot');
        $trades = config('mod_game.bots');
        if($last + 1 > (count($trades) - 1)){
            $last = 0;
            $this->redis->set('last.bot', $last);
        } else {
            $last += 1;
            $this->redis->set('last.bot', $last);
        }
        return redirect($trades[$last]);
    }
    private function _parseItems(&$items, &$missing = false, &$price = false)
    {
        $itemInfo = [];
        $total_price = 0;
        $i = 0;

        foreach ($items as $item) {
            $value = $item['classid'];
            if ($item['appid'] != config('mod_game.appid')) {
                $missing = true;
                break;
            }
            if (!isset($itemInfo[$value])){
                $info = new Item($item);
                $itemInfo[$value] = $info;
            }
            if(Item::pchk($itemInfo[$value])){
                $total_price = $total_price + $itemInfo[$value]->price;
                $items[$i]['price'] = $itemInfo[$value]->price;
                unset($items[$i]['appid']);
                $i++;
            } else {
                $price = true;
                break;
            }
        }
        return $total_price;
    }
    public function currentGame()
    {
        $game = Game::orderBy('id', 'desc')->first();
        if (is_null($game)) $game = $this->newGame();
        $bets = $game->bets()->with(['user', 'game'])->get()->sortByDesc('created_at');
        if (!is_null($this->user)) $user_chance = $this->_getUserChanceOfGame($this->user, $game);
        $chances = json_encode($this->_getChancesOfGame($game));
        if (!is_null($this->user)) $user_items = $this->user->itemsCountByGame($game);
        
        $gifts = []; $giftsdb = DB::table('gifts')->where('sold', 1)->orderBy('sold_at', 'desc')->limit(20)->get();
        foreach($giftsdb as $gift){$user = User::find($gift->user_id);$gifts[] = ['avatar' => $user->avatar,'game' => $gift->game_name,'price' => $gift->store_price];}
        $gifts = (object)$gifts; 
        $have_gift = 'false';
        $game_name = '';
        $store_price = 0;
        if(!Auth::guest()){
            $gift = DB::table('gifts')->where('user_id', $this->user->id)->where('received', 0)->first();
            if(!is_null($gift)){
                $have_gift = 'true';
                $game_name = $gift->game_name;
                $store_price = $gift->store_price;
            }
        }
        ShopController::checkSales();
        $dsales = DB::table('shop')->where('status', Shop::ITEM_STATUS_FOR_SALE)->where('sale', 1)->orderBy('price', 'desc')->limit(8)->get();
        $sales = [];
        foreach ($dsales as $item){
            $sales[] = ['id' => $item->id, 'name' => $item->name, 'price' => round($item->price*0.9,2), 'oldprice' => $item->price, 'classid' => $item->classid, 'className' => Shop::getClassRarity($item->rarity) ];
        }
        $bgifts = DB::table('gifts')->orderBy('store_price', 'desc')->get();
        parent::setTitle(round($game->price) . ' р. | ');
        return view('pages.index', compact('game', 'bets', 'user_chance', 'chances', 'user_items', 'gifts', 'sales', 'bgifts', 'have_gift', 'game_name', 'store_price'));
    }
    public function getLastGame()
    {
        $game = Game::orderBy('id', 'desc')->first();
        if (is_null($game)) $game = $this->newGame();
        return $game;
    }
    public function getCurrentGame()
    {
        $this->game->winner;
        return $this->game;
    }
    public function dec()
    {
        $sumtd = DB::table('games')->where('status', Game::STATUS_FINISHED)->where('created_at', '>=', Carbon::today())->sum('price');
        $sumfw = DB::table('games')->where('status', Game::STATUS_FINISHED)->where('created_at', '>=', Carbon::today()->subWeek())->sum('price');
        $sumfr = DB::table('games')->where('status', Game::STATUS_FINISHED)->sum('price');
        $echo = 'Разыграно сегодня: ' . $sumtd . ' | Комиссия: ~' . ($sumtd * 0.09) . '<br> Разыграно за неделю: ' . $sumfw . ' | Комиссия: ~' . ($sumfw * 0.09) . '<br> Разыграно всего: ' . $sumfr . ' | Комиссия: ~' . ($sumfr * 0.09);
        return $echo;
    }
    private function genRand(){
        $rand_number = "0.";
        $firstrand = mt_rand(20, 80);
        if(mt_rand(0, config('mod_game.game_low_chanse')) == 0) $firstrand = mt_rand(3, 96);
        if(mt_rand(0, (config('mod_game.game_low_chanse') * 2)) == 0) $firstrand = mt_rand(0, 9) . mt_rand(0, 9);
        if(strlen($firstrand) < 2) $firstrand = "0" . $firstrand;
        $rand_number .= $firstrand;
        for($i = 1; $i < 15; $i++) {
            $rand_number .= mt_rand(0, 9);
        }
        $rand_number .= mt_rand(1, 9);
        return $rand_number;
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
    public static function _fixGame($id)
    {
        $game = Game::where('id', $id)->first();
        $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
        $bets = Bet::where('game_id', $id)->where('user_id', '!=', $bonus->id)->orderBy('id')->get();
        $lastTicket = 0;
        foreach ($bets as $bet){
            $B = Bet::where('id', $bet->id)->first();
            $B->from = $lastTicket + 1;
            $B->to = $B->from + ($B->price * 100) - 1;
            $B->save();
            $lastTicket = $B->to;
        }
        $game = Game::where('id', $id)->first();
        $game->price = $lastTicket/100;
        $chance = 0;
        if (!is_null($game->winner)) {
            if ($game->winner->steamid64 != config('mod_game.bonus_bot_steamid64')) {
                $bet = Bet::where('game_id', $game->id)
                    ->where('user_id', $game->winner->id)
                    ->sum('price');
                if ($game->price > 0 && $bet) $chance = round($bet / $game->price, 3) * 100; 
            } else {
                $chance = 0;
            }
        }
        $game->chance = $chance;
        $game->save();
        return;
    }
    public function getWinners()
    {
        $us = $this->game->users();
        self::_fixGame($this->game->id);
        $lastBet = Bet::where('game_id', $this->game->id)->orderBy('to', 'desc')->first();
        $winTicket = WinnerTicket::where('game_id', $this->game->id)->first();
        if($winTicket == null) {
            $winTicket = ceil($this->game->rand_number * $lastBet->to);
        } else {
            $winTicket = $winTicket->winnerticket;
            $this->game->rand_number = ($winTicket- 0.1)/$lastBet->to;
            if(strlen($this->game->rand_number)<19) {
                $diff = 19 - strlen($this->game->rand_number);
                $min = "1";
                $max = "9";
                for($i = 1; $i < $diff; $i++) {
                    $min .= "0";
                    $max .= "9";
                }
                $this->game->rand_number = $this->game->rand_number."".  rand($min, $max);
            }
            DB::table('winner_tickets')->truncate();
        }
        $winningBet = Bet::where('game_id', $this->game->id)->where('from', '<=', $winTicket)->where('to', '>=', $winTicket)->first();
        $this->game->winner_id      = $winningBet->user_id;
        $this->game->price             = $lastBet->to/100;
        $this->game->status         = Game::STATUS_FINISHED;
        $this->game->finished_at    = Carbon::now();
        $chance = $this->_getUserChanceOfGame($this->game->winner, $this->game);
        $items = $this->sendItems($this->game->bets, $this->game->winner, $chance);
        $this->game->won_items      = json_encode($items['itemsInfo']);
        $this->game->comission      = json_encode($items['commissionItems']);
        if($items['countbb'] == 0) $this->game->status_prize = 1;
        $this->game->chance         = $chance;
        $this->game->save();
        $users = [];
        foreach ($us as $usr) for($i = 1; $i < round($this->_getUserChanceOfGame($usr, $this->game)); $i++) $users[] = $usr;
        $returnValue = [
            'game'   => $this->game,
            'winner' => $this->game->winner,
            'round_number' => $this->game->rand_number,
            'ticket' => $winTicket,
            'tickets' => $lastBet->to,
            'users' => $us,
            'userchanses' => $users,
            'chance' => $chance,
            'winb_id' => $winningBet->id
        ];
        return response()->json($returnValue);
    }
    public function sendItems($bets, $user, $chance){
        $itemsInfo = [];
        $items = [];
        $commission = config('mod_game.comission');
        $commissionItems = [];
        $tempPrice = 0;
        $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
        $firstBet = Bet::where('game_id', $this->game->id)->where('user_id', '!=' , $bonus->id)->orderBy('created_at', 'asc')->first();
        if(!is_null($firstBet)){
            if ($firstBet->user == $user) $commission = $commission - config('mod_game.comission_first_bet');
        }
        $name = strtolower($user->username);
        if (strpos(strtolower(' '.$name),  strtolower(config('app.sitename'))) != false) $commission = $commission - config('mod_game.comission_site_nick');
        $commissionPrice = round(($this->game->price / 100) * $commission);
        $cardSum = 0;
        foreach ($bets as $bet) {
            $betItems = json_decode($bet->items, true);
            foreach ($betItems as $item) {
                if (($bet->user_id == $user->id) && ($chance >= config('mod_game.comission_minchance'))) {
                    $itemsInfo[] = $item;
                    if (!isset($item['classid'])) {
                        $cardSum += $item['price'];
                    }
                } else {
                    $items[] = $item;
                }
            }
        } 
        foreach ($items as $item) {
            if (!isset($item['price'])) $item['price'] = 0.1;
        }
        uasort($items,function($f1,$f2){
            if($f1['price'] < $f2['price']) return 1;
            elseif($f1['price'] > $f2['price']) return -1;
            else return 0;
        });
        foreach ($items as $item) {
            if ((($item['price'] + $tempPrice) <= $commissionPrice)) {
                $commissionItems[] = $item;
                $tempPrice = $tempPrice + $item['price'];
            } else {
                $itemsInfo[] = $item;
                if (!isset($item['classid'])) {
                    $cardSum += $item['price'];
                }
            }
        }
        User::mchange($user->id, $cardSum);
        $this->redis->publish(self::LOG_CHANNEL, json_encode('Победил: '. $user->username . ' | Шанс на победу: '.$chance . ' | Комиссия: '.$tempPrice));
        if (config('mod_game.bonus_bot')) {
            $bonusdrop = DB::table('bonus_items')->first();
            $botid = false;
            if(is_null($bonusdrop)){
                $bonusItemsPrice =  round(($this->game->price / 100),2);
                if ($bonusItemsPrice > 5) $bonusItemsPrice = 5.00;
                if ($bonusItemsPrice < 0.01) $bonusItemsPrice = 0.01;
                $bonusitems = [];
                $bonusitem = [
                    'id' => $bonusItemsPrice,
                    'img' => '/assets/img/card.png',
                    'price' => $bonusItemsPrice,
                    'name' => 'Карточка на ' . $bonusItemsPrice . ' руб.',
                    'style' => '-webkit-filter: hue-rotate(' . $bonusItemsPrice * 10 . 'deg)'
                ];
            } else {
                $bonusitem = json_decode($bonusdrop->item, true);
                $bonusItemsPrice = $bonusdrop->price;
                $botid = $bonusdrop->bot_id;
                DB::table('bonus_items')->where('id', $bonusdrop->id)->delete();
            }
            $bonusitems[] = $bonusitem;
            $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
            $returnValue = [
                'offerid' => 0,
                'userid' => $bonus->id,
                'message' => '',
                'steamid64' => $bonus->steamid64,
                'gameid' => $this->game->id,
                'items' => $bonusitems,
                'price' => $bonusItemsPrice,
                'success' => true
            ];
            if($botid) $returnValue['botid'] = $botid;
            $this->redis->lpush('bets.list', json_encode($returnValue)); 
        }
        $ncitems = $itemsInfo;
        $botbets = Bot_bet::where('game_id', $this->game->id)->where('status', 0)->get();
        if(count($botbets)){
            foreach($botbets as $bet){
                $userItems = [];
                $shopItems = [];
                $bitems = array_values(json_decode($bet->items, true));
                foreach ($bitems as $key => $item ) {
                    if (in_array($item, $ncitems)){
                        $ckey = array_search($item, $ncitems);
                        unset($ncitems[$ckey]);
                        $userItems[] = $item['classid'];
                    } else {
                        $shopItems[] = $item['classid'];
                    }
                }
                $valueUser = [
                    'appId' => config('mod_game.appid'),
                    'steamid' => $user->steamid64,
                    'accessToken' => $user->accessToken,
                    'items' => $userItems,
                    'game' => $this->game->id
                ];
                $bet->items_won = json_encode($userItems);
                $bet->save();
                $this->redis->rpush('b'. $bet->botid.'_'.self::SEND_OFFERS_LIST, json_encode($valueUser));
                if (config('mod_game.comission_to_shop') && count($shopItems)) {
                    $shop_id = Shop::selectBot();
                    $shop = Shop::parceTradeLinkShop(config('mod_shop.bots')[$shop_id]);
                    if ($shop != NULL) {
                        $valueShop = [
                            'appId' => config('mod_game.appid'),
                            'steamid' => (string)$shop['steamid64'],
                            'accessToken' => (string)$shop['accessToken'],
                            'items' => $shopItems,
                            'game' => 0
                        ];
                        $this->redis->rpush('b'. $bet->botid.'_'.self::SEND_OFFERS_LIST, json_encode($valueShop));
                    }
                }
            }
        }
        $response = [
            'itemsInfo' => $itemsInfo,
            'commissionItems' => $commissionItems,
            'countbb' => count($botbets)
        ];
        return $response;
    }
    
    public function setGameStatus(Request $request)
    {
        if ($request->get('status') == Game::STATUS_PRE_FINISH)
            $this->redis->set('last.ticket.' . $this->game->id, 0);
        $this->game->status = $request->get('status');
        $this->game->save();
        return $this->game;
    }
    
    public function setPrizeStatus(Request $request)
    {
        $botbet = Bot_bet::where(['game_id' => $request->get('game'), 'botid' => $request->get('botid')])->first();
        if(is_null($botbet)){return;}
        $botbet->status = $request->get('status');
        $botbet->save();
        $game = Game::find($botbet->game_id);
        $user = User::find($game->winner_id);
        $bot_bets_w = Bot_bet::where('game_id', $request->get('game'))->where('status', 0)->get();
        $bot_bets_e = Bot_bet::where('game_id', $request->get('game'))->where('status', 2)->get();
        if(!count($bot_bets_w) && !count($bot_bets_e)){
            $game->status_prize = 1;
            $game->save();
        } else {
            if(count($bot_bets_e)){
                $game->status_prize = 2;
                $game->save();
            } else {
                $game->status_prize = 0;
                $game->save();
            }
        }
        return $game;
    } 
    public function fixRequest(Request $request){
        $gameid = $request->get('game_id'); $count = 0;
        if ($gameid == '*'){
            $games = DB::table('games')->where('status_prize', 2)->take(10)->orderBy('created_at', 'desc')->get();
            foreach($games as $game){
                $count = self::fixBotBets($game->id);
            }
        } else {
            $count = self::fixBotBets($gameid);
        }
        return back();
    }
    public function checkBrokenGames(Request $request){
        $games = DB::table('games')->where('status_prize', 2)->take(10)->orderBy('created_at', 'desc')->get();
        $count = 0;
        foreach($games as $game){
            $count = self::fixBotBets($game->id);
        }
        return response()->json(['success' => true, 'count' => $count]);
    }
    private function fixBotBets($gameid){
        $game = Game::find($gameid);
        if(is_null($game)) return 0;
        $bot_bets_e = Bot_bet::where('game_id', $gameid)->where('status', 2)->where('enum', '<', 5)->get();
        $game = Game::find($gameid);
        if(count($bot_bets_e)){
            $user = User::find($game->winner_id);
            $game->status_prize = 0;
            $game->save();
            foreach($bot_bets_e as $bet){
                $bet->status = 0;
                $bet->enum = $bet->enum + 1;
                $bet->save();
                if($bet->items_won != ''){
                    $bitems = array_values(json_decode($bet->items_won, true));
                } else {
                    $bitems = [];
                    $baitems = array_values(json_decode($bet->items, true));
                    foreach ($baitems as $key => $item ) {
                        $bitems[] = $item['classid'];
                    }
                }
                $valueUser = [
                    'appId' => config('mod_game.appid'),
                    'steamid' => $user->steamid64,
                    'accessToken' => $user->accessToken,
                    'items' => $bitems,
                    'game' => $gameid
                ];
                $this->redis->rpush('b'. $bet->botid.'_'.self::SEND_OFFERS_LIST, json_encode($valueUser));
            }
        } else {
            $game->status_prize = 1;
            $game->save();
        }
        return count($bot_bets_e);
    }

    public function newGame(){
        if(!is_null($this->game)){
            $rand = DB::table('winner_rands')->where('game_id', $this->game->id + 1)->first();
            if(is_null($rand)) {
                $rand_number = self::genRand();
            } else {
                $rand = $rand->randn;
                if(strlen($rand)<19) {
                    $diff = 19 - strlen($rand);
                    $min = "1";
                    $max = "9";
                    for($i = 1; $i < $diff; $i++) {
                        $min .= "0";
                        $max .= "9";
                    }
                    $rand = $rand . "" . rand($min, $max);
                }
                $rand_number = $rand;
                DB::table('winner_rands')->truncate();
            }
        } else {
            $rand_number = self::genRand();
        }
        
        $game = Game::create(['rand_number' => $rand_number]);
        $game->hash = md5($game->rand_number);
        $game->rand_number = 0;
        $this->game = $game;
        $this->redis->set('current.game', $game->id);
        $this->redis->set('last.ticket.' . $this->game->id, 0);
        CCentrifugo::publish('update_p' , ['type' => 'classic', 'price' => 0]);
        \Cache::put('new_game', 'new_game', 1);
        return $game;
    }
    public static function object_to_array($data){
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = self::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
    public function checkOffer(Request $request){
        $botid = $request->get('botid');
        $data = $this->redis->lrange('b'.$botid.'_check.list', 0, -1);
        foreach ($data as $offerJson) {
            $offer = json_decode($offerJson);
            $accountID = $offer->accountid;
            $items = json_decode($offer->items, true);
            $itemsCount = count($items);

            $user = User::where('steamid64', $accountID)->first();
            if (is_null($user)) {
                $this->redis->lrem('usersQueue.list', 1, $accountID);
                $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                continue;
            } else {
                if (empty($user->accessToken)) {
                    $this->redis->lrem('usersQueue.list', 1, $accountID);
                    $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                    $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                    $this->_responseErrorToSite('Введите трейд ссылку!', $accountID, self::BET_DECLINE_CHANNEL);
                    continue;
                }
            }
            if (!isset($offer->message)){
                $offer->message = '';
            } else {
                $message = mb_strtolower($offer->message);
                $message = ChatController::censrepl($message);
                $offer->message = $message;
            }
            if($offer->message != 'bonus'){
                $totalItems = $user->itemsCountByGame($this->game);
                if ($itemsCount > config('mod_game.max_items_per_trade')) {
                    $this->redis->lrem('usersQueue.list', 1, $accountID);
                    $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                    $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                    $this->_responseErrorToSite('Максимальное кол-во предметов за обмен - ' . config('mod_game.max_items_per_trade'), $accountID, self::BET_DECLINE_CHANNEL);
                }
                if (($itemsCount + $totalItems) > config('mod_game.max_items')) {
                    $this->_responseErrorToSite('Максимальное кол-во предметов для - ' . config('mod_game.max_items') . '; ' . ($itemsCount + $totalItems - config('mod_game.max_items')) . ' предметов уйдет на следущую игру', $accountID, self::BET_DECLINE_CHANNEL);
                }
            }
            $total_price = $this->_parseItems($items, $missing, $price);
            if($offer->message != 'bonus'){
                if ($missing) {
                    $this->_responseErrorToSite('Принимаются только предметы из CS:GO', $accountID, self::BET_DECLINE_CHANNEL);
                    $this->redis->lrem('usersQueue.list', 1, $accountID);
                    $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                    $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                    continue;
                }

                if ($price) {
                    $this->_responseErrorToSite('В вашем трейде есть предметы, цены которых мы не смогли определить', $accountID, self::BET_DECLINE_CHANNEL);
                    $this->redis->lrem('usersQueue.list', 1, $accountID);
                    $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                    $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                    continue;
                }
                if ($total_price < config('mod_game.min_price')) {
                    $this->_responseErrorToSite('Минимальная сумма депозита ' . config('mod_game.min_price') . 'р.', $accountID, self::BET_DECLINE_CHANNEL);
                    $this->redis->lrem('usersQueue.list', 1, $accountID);
                    $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
                    $this->redis->rpush('b'.$botid.'_decline.list', $offer->offerid);
                    continue;
                }
            }
            $returnValue = [
                'offerid' => $offer->offerid,
                'message' => $offer->message,
                'userid' => $user->id,
                'steamid64' => $user->steamid64,
                'gameid' => $this->game->id,
                'items' => $items,
                'price' => $total_price,
                'botid' => $botid,
                'success' => true
            ];
            $this->_responseMessageToSite('Обмен №' . $offer->offerid . ' на ' . $total_price . 'р. принимается', $user->steamid64);
            $bet = (object)['id' => 0, 'user' => $user,'items' => json_encode($items),'itemsCount' => count($items),'price' => $total_price,'from' => 0,'to' => 0,'msg' => $offer->message,'vip' => false];
            $value = [
                'html' => view('includes.bet', compact('bet'))->render(),
                'steamid' => $user->steamid64
            ];
            CCentrifugo::publish(self::VIEW_BET_CHANNEL.'#'.$user->steamid64, $value);
            
            $this->redis->rpush('b'.$botid.'_checked.list', json_encode($returnValue));
            $this->redis->lrem('b'.$botid.'_check.list', 0, $offerJson);
        }
        return response()->json(['success' => true]);
    }
    public function newBet(){
        if (\Cache::has('new_game')) return $this->_responseSuccess();
        $data = $this->redis->lrange('bets.list', 0, -1);
        foreach ($data as $newBetJson) {
            $newBet = json_decode($newBetJson, true);
            $user = User::find($newBet['userid']);
            $this->game = $this->getLastGame();
            if (is_null($user)) continue;
            if ($user->ban == 0){
                if ($this->game->id < $newBet['gameid']) continue;
                if ($this->game->id >= $newBet['gameid']) $newBet['gameid'] = $this->game->id;
                if ($this->game->status == Game::STATUS_PRE_FINISH || $this->game->status == Game::STATUS_FINISHED) {
                    $this->_responseMessageToSite('Ваша ставка пойдёт на следующую игру.', $user->steamid64);
                    $this->redis->lrem('bets.list', 0, $newBetJson);
                    $newBet['gameid'] = $newBet['gameid'] + 1;
                    $this->redis->rpush('bets.list', json_encode($newBet));
                    continue;
                }
                if($newBet['message'] == 'bonus'){
                    foreach ($newBet['items'] as $item) DB::table('bonus_items')->insert(['item' => json_encode($item),'price' => $item['price'],'bot_id' => $newBet['botid']]);
                    $this->redis->lrem('bets.list', 0, $newBetJson);
                    continue;
                }
                $this->redis->lrem('bets.list', 0, $newBetJson);
                $totalItems = $user->itemsCountByGame($this->game);
                if (($totalItems + count($newBet['items'])) > config('mod_game.max_items')) {
                    $thisitems = []; $nextitems = []; $nextBet = $newBet;
                    $thisitemsprice = 0; $nextitemsprice = 0;
                    foreach ($newBet['items'] as $item){
                        if (count($thisitems) + $totalItems < config('mod_game.max_items') ){
                            $thisitems[] = $item;
                            $thisitemsprice += $item['price'];
                        } else {
                            $nextitems[] = $item;
                            $nextitemsprice += $item['price'];
                        }
                    }
                    $this->_responseMessageToSite(''. count($nextitems) . ' предметов пойдет на следующую игру', $user->steamid64);
                    $nextBet['gameid'] = $newBet['gameid'] + 1;
                    $nextBet['items'] = $nextitems;
                    $nextBet['price'] = $nextitemsprice;
                    $newBet['items'] = $thisitems;
                    $newBet['price'] = $thisitemsprice;
                    $this->redis->rpush('bets.list', json_encode($nextBet));
                }
                if(count($newBet['items']) == 0) continue;
                $this->lastTicket = $this->redis->get('last.ticket.' . $this->game->id);
                $ticketFrom = $this->lastTicket + 1;
                $ticketTo = $ticketFrom + ($newBet['price'] * 100) - 1;
                if ($user->steamid64 == config('mod_game.bonus_bot_steamid64')){
                    $ticketFrom = 0;
                    $ticketTo = 0;
                }
                if ($user->steamid64 != config('mod_game.bonus_bot_steamid64')) $this->redis->set('last.ticket.' . $this->game->id, $ticketTo);
                $vip = 0; if (strpos(strtolower(' '.$user->username),  strtolower(config('app.sitename'))) != false) $vip = 1;
                $lastBet = Bet::find(\DB::table('bets')->max('id'));
                if(is_null($lastBet)){
                    $bet = new Bet();
                    $bet->user()->associate($user);
                    $bet->items = json_encode($newBet['items']);
                    $bet->itemsCount = count($newBet['items']);
                    $bet->price = $newBet['price'];
                    $bet->from = $ticketFrom;
                    $bet->to = $ticketTo;
                    $bet->game()->associate($this->game);
                    $bet->msg = $newBet['message'];
                    $bet->vip = $vip;
                    $bet->save();
                } else {
                    if ($lastBet->user_id != $newBet['userid'] || $lastBet->game_id != $this->game->id) {
                        $bet = new Bet();
                        $bet->user()->associate($user);
                        $bet->items = json_encode($newBet['items']);
                        $bet->itemsCount = count($newBet['items']);
                        $bet->price = $newBet['price'];
                        $bet->from = $ticketFrom;
                        $bet->to = $ticketTo;
                        $bet->game()->associate($this->game);
                        $bet->msg = $newBet['message'];
                        $bet->vip = $vip;
                        $bet->save();
                    } else {
                        $items = [];
                        $lastBetItems = json_decode($lastBet->items);
                        foreach ($lastBetItems as $i){
                            $items[] = $i;
                        }
                        foreach ($newBet['items'] as $i){
                            $items[] = $i;
                        }
                        $lastBet->items = json_encode($items);
                        $lastBet->itemsCount = count($items);
                        $lastBet->price = $lastBet->price + $newBet['price'];
                        $lastBet->to = $ticketTo;
                        $lastBet->save();
                        $bet = $lastBet;
                    }
                }
                User::slchange($user->id, $newBet['price'] / 100  * config('mod_game.slimit'));
                if(isset($newBet['botid'])){
                    $bot_bet = Bot_bet::where('game_id', $this->game->id)->where('botid', $newBet['botid'])->first();
                    if(is_null($bot_bet)){
                        Bot_bet::create(['botid'=>$newBet['botid'],'game_id'=>$this->game->id,'items'=>json_encode($newBet['items'])]);
                    } else {
                        $newitems = [];
                        $items = json_decode($bot_bet->items);
                        foreach($items as $item){
                            $newitems[] = $item;
                        }
                        foreach($newBet['items'] as $item){
                            $newitems[] = $item;
                        }
                        $bot_bet->items = json_encode($newitems);
                        $bot_bet->save();
                    }
                }
                $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
                $bets = Bet::where('game_id', $this->game->id)->where('user_id','!=', $bonus->id)->get();
                $this->game->items = $bets->sum('itemsCount');
                $this->game->price = $bets->sum('price');

                if (((count($this->game->users()) >= config('mod_game.players_to_start')) && ($this->game->price >= config('mod_game.game_min_price'))) || $this->game->items >= config('mod_game.game_items')) {
                    $this->game->status = Game::STATUS_PLAYING;
                    $this->game->started_at = Carbon::now();
                }

                if ($this->game->items >= config('mod_game.game_items')) {
                    $this->game->status = Game::STATUS_FINISHED;
                    $this->redis->publish(self::SHOW_WINNERS, true);
                }
                $this->game->save();
                $this->redis->publish(self::LOG_CHANNEL, json_encode('Ставка: '.$newBet['price'].' р. | '.$user->username));
                $bettemp = $bet;
                $cc = '';
                $lastbets = DB::table('bets')->where('game_id', $this->game->id)->orderBy('id')->get();
                foreach ($lastbets as $lastbet) {
                    $lastuser =  DB::table('users')->where('id', $lastbet->user_id)->first();
                    $bet = $lastbet;
                    $bet->user = $lastuser;
                    $bet->game = $this->game;
                    $cc = view('includes.cc', compact('bet'))->render().$cc;
                }
                $bet = $bettemp;
                $chances = $this->_getChancesOfGame($this->game);
                $returnValue = [
                    'betId' => $bet->id,
                    'userId' => $user->steamid64,
                    'cc' => $cc,
                    'html' => view('includes.bet', compact('bet'))->render(),
                    'itemsCount' => $this->game->items,
                    'gamePrice' => $this->game->price,
                    'gameStatus' => $this->game->status,
                    'bettype' => 0,
                    'betprice' => $newBet['price'],
                    'chances' => $chances
                ];
                $this->redis->publish(self::NEW_BET_CHANNEL, json_encode($returnValue));
                CCentrifugo::publish(self::NEW_BET_CHANNEL, $returnValue);
                CCentrifugo::publish('update_p' , ['type' => 'classic', 'price' => $this->game->price]);
            } else {
                $this->redis->lrem('bets.list', 0, $newBetJson);
                $this->_responseMessageToSite('Вы забанены на сайте.', $user->steamid64);
            }
        }
        return $this->_responseSuccess();
    }
    public function addTicket(Request $request){
        if (\Cache::has('new_game')) return response()->json(['text' => 'Подождите...', 'type' => 'error']);
        if ($this->user->ban != 0) return response()->json(['text' => 'Вы забанены на сайте.', 'type' => 'error']);
        $totalItems = $this->user->itemsCountByGame($this->game);
        if ($totalItems > config('mod_game.max_items') || (1 + $totalItems) > config('mod_game.max_items')) return response()->json(['text' => 'Максимальное кол-во предметов для депозита - ' . config('mod_game.max_items'), 'type' => 'error']);
        if ($this->user->trade_link == "") return response()->json(['text' => 'Не установлена ссылка на обмен', 'type' => 'error']);
        if (!$request->has('sum')) return response()->json(['text' => 'Ошибка. Укажите сумму ставки.', 'type' => 'error']);
        $this->game = $this->getLastGame();
        if ($this->game->status == Game::STATUS_PRE_FINISH || $this->game->status == Game::STATUS_FINISHED) return response()->json(['text' => 'Дождитесь следующей игры!', 'type' => 'error']);
        $sum = floor($request->get('sum')*100)/100;
        if ($sum < 0.1) return response()->json(['text' => 'Минимальная ставка 0.1р.', 'type' => 'error']);
        $ticket = (object)[
            'id' => $sum,
            'img' => '/assets/img/card.png',
            'price' => $sum,
            'name' => 'Карточка на ' . $sum . ' руб.',
            'style' => '-webkit-filter: hue-rotate(' . $sum * 10 . 'deg)'
        ];
        if (is_null($ticket)){
            return response()->json(['text' => 'Ошибка.', 'type' => 'error']);
        } else {
            if (!User::mchange($this->user->id, -$ticket->price)) return response()->json(['text' => 'Недостаточно средств на балансе', 'type' => 'error']);
            $this->lastTicket = $this->redis->get('last.ticket.' . $this->game->id);
            $ticketFrom = $this->lastTicket + 1; $ticketTo = $ticketFrom + ($ticket->price * 100) - 1;
            $this->redis->set('last.ticket.' . $this->game->id, $ticketTo);
            $vip = 0; if (strpos(strtolower(' '.$this->user->username),  strtolower(config('app.sitename'))) != false) $vip = 1;
            $lastBet = Bet::find(\DB::table('bets')->max('id'));
            if ($lastBet === NULL || $lastBet->user_id != $this->user->id || $lastBet->game_id != $this->game->id) {
                $bet = new Bet();
                $bet->user()->associate($this->user);
                $bet->items = json_encode([$ticket]);
                $bet->itemsCount = 1;
                $bet->price = $ticket->price;
                $bet->from = $ticketFrom;
                $bet->to = $ticketTo;
                $bet->game()->associate($this->game);
                $bet->vip = $vip;
                $bet->msg = '';
                $bet->save();
            } else {
                $items = [];
                $lastBetItems = json_decode($lastBet->items);
                foreach ($lastBetItems as $i){
                    $items[] = $i;
                }
                $items[] = $ticket;
                $lastBet->items = json_encode($items);
                $lastBet->itemsCount = $lastBet->itemsCount + 1;
                $lastBet->price = $lastBet->price + $ticket->price;
                $lastBet->to = $ticketTo;
                $lastBet->save();
                $bet = $lastBet;
            }
            User::slchange($this->user->id, $ticket->price / 100 * config('mod_game.slimit'));
            $this->redis->publish(self::LOG_CHANNEL, json_encode('Ставка: '.$ticket->price.' р. | '.$this->user->username));
            $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
            $bets = Bet::where('game_id', $this->game->id)->where('user_id','!=', $bonus->id)->get();
            $this->game->items = $bets->sum('itemsCount');
            $this->game->price = $bets->sum('price');
            if (((count($this->game->users()) >= config('mod_game.players_to_start')) && ($this->game->price >= config('mod_game.game_min_price'))) || $this->game->items >= config('mod_game.game_items')) {
                $this->game->status = Game::STATUS_PLAYING;
                $this->game->started_at = Carbon::now();
            }
            if ($this->game->items >= config('mod_game.game_items')) {
                $this->game->status = Game::STATUS_FINISHED;
                $this->redis->publish(self::SHOW_WINNERS, true);
            }
            $this->game->save();
            $chances = $this->_getChancesOfGame($this->game);
            $bettemp = $bet;
            $cc = '';
            $lastbets = DB::table('bets')->where('game_id', $this->game->id)->orderBy('id')->get();
            foreach ($lastbets as $lastbet) {
                $lastuser =  DB::table('users')->where('id', $lastbet->user_id)->first();
                $bet = $lastbet;
                $bet->user = $lastuser;
                $bet->game = $this->game;
                $cc = view('includes.cc', compact('bet'))->render().$cc;
            }
            $bet = $bettemp;
            $returnValue = [
                'betId' => $bet->id,
                'userId' => $this->user->steamid64,
                'cc' => $cc,
                'html' => view('includes.bet', compact('bet'))->render(), //$html,
                'itemsCount' => $this->game->items,
                'gamePrice' => $this->game->price,
                'gameStatus' => $this->game->status,
                'bettype' => 1,
                'betprice' => $ticket->price,
                'chances' => $chances
            ];
            $this->redis->publish(self::NEW_BET_CHANNEL, json_encode($returnValue));
            CCentrifugo::publish(self::NEW_BET_CHANNEL, $returnValue);
            CCentrifugo::publish('update_p' , ['type' => 'classic', 'price' => $this->game->price]);
            return response()->json(['text' => 'Действие выполнено.', 'type' => 'success']);
        }
    }

    public function getBalance()
    {
        return $this->user->money;
    }
    
    public function getSlimit()
    {
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
        return $slimit;
    }

    public static function _getChancesOfGame($game)
    {
        $chances = [];
        foreach ($game->users() as $user) {
            $vip = false;
            if (strpos(strtolower(' '.$user->username),  strtolower(config('app.sitename'))) != false) $vip = true;
            $chances[] = [
                'chance' => self::_getUserChanceOfGame($user, $game),
                'items' => User::find($user->id)->itemsCountByGame($game),
                'steamid64' => $user->steamid64,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'vip' => $vip
            ];
        }
        uasort($chances,function($f1,$f2){
            if($f1['chance'] < $f2['chance']) return 1;
            elseif($f1['chance'] > $f2['chance']) return -1;
            else return 0;
        });
        $chs = $chances;
        $chances = [];
        foreach ($chs  as $ch) {
            if($ch['steamid64'] == config('mod_game.bonus_bot_steamid64'))$ch['chance'] = 'BONUS ';
            $chances[] = $ch;
        }
        return $chances;
    }
    public static function getLastBet(){
        return DB::table('bets')->max('id');
    }
    public static function _getUserChanceOfGame($user, $game)
    {
        $chance = 0;
        if (!is_null($user)) {
            if ($user->steamid64 != config('mod_game.bonus_bot_steamid64')) {
                $bet = Bet::where('game_id', $game->id)
                    ->where('user_id', $user->id)
                    ->sum('price');
                if ($game->price > 0 && $bet) $chance = round($bet / $game->price, 3) * 100; 
            } else {
                $chance = 0;
            }
        }
        return $chance;
    }

    private function _responseErrorToSite($message, $user, $channel)
    {
        CCentrifugo::publish($channel, ['user' => $user,'msg' => $message]);
    }

    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }

    private function _responseSuccess(){
        return response()->json(['success' => true]);
    }
    
    public static function havegame($user){
        $has = false;
        $jsonResponse = self::curl('http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=' . env('STEAM_APIKEY','') . '&steamid=' . $user->steamid64 . '&format=json');
        $Response = json_decode($jsonResponse, true);
        $jsonGames = $Response['response'];
        if(isset($jsonGames['games'])){
            $Games = $jsonGames['games'];
            foreach ($Games as $Game) {
                if ($Game['appid'] == config('mod_game.appid')) {
                    $has = true;
                    continue;
                }
            }
        }
        return response()->json($has);
    }    

    public function curcomm(){
        $my_comission = config('mod_game.comission');
        if (!is_null($this->user)){
            $bonus = User::where('steamid64', config('mod_game.bonus_bot_steamid64'))->first();
            $firstBet = Bet::where('game_id', $this->game->id)->where('user_id', '!=' , $bonus->id)->orderBy('created_at', 'asc')->first();
            if (!is_null($firstBet)){
                if ($firstBet->user == $this->user) $my_comission = $my_comission - config('mod_game.comission_first_bet');
            }
            if (strpos(strtolower(' '.$this->user->username),  strtolower(config('app.sitename'))) != false) $my_comission = $my_comission - config('mod_game.comission_site_nick');
        }
        return response()->json($my_comission);
    }
    public function lw(){
        $lastgame = DB::table('games')->where('id', DB::table('games')->max('id'))->first();
        if (!is_null($lastgame)){
            if ($lastgame->status == Game::STATUS_FINISHED) {
                $user = User::where('id', $lastgame->winner_id)->first();
                unset($user->password);
                $lw = [
                    'user' => $user,
                    'price' => $lastgame->price,
                    'chance' => self::_getUserChanceOfGame($user, $lastgame)
                ];
            } else {
                $lastgame = DB::table('games')->where('id', (\DB::table('games')->max('id')) - 1)->first();
                if (!is_null($lastgame)){
                    $user = User::where('id', $lastgame->winner_id)->first();
                    unset($user->password);
                    $lw = [
                        'user' => $user,
                        'price' => $lastgame->price,
                        'chance' => self::_getUserChanceOfGame($user, $lastgame)
                    ];
                } else {
                    $u = [
                        'avatar' => '/assets/img/blank.jpg',
                        'username' => 'Пока не выбран',
                        'steamid64' => ''
                    ];
                    $lw = [
                        'user' => $u,
                        'price' => '???',
                        'chance' => '???'
                    ];
                }
            }    
        } else {
            $u = [
                'avatar' => '/assets/img/blank.jpg',
                'username' => 'Пока не выбран',
                'steamid64' => ''
            ];
            $lw = [
                'user' => $u,
                'price' => '???',
                'chance' => '???'
            ];
        }
        return $lw;
    }    
    public function mlfv(){
        $u = ['avatar' => '/assets/img/blank.jpg','username' => 'Пока не выбран','steamid64' => ''];
        $mlfv = ['user' => $u,'price' => '???','chance' => '???'];
        $mlfgame = DB::table('games')->where('status', Game::STATUS_FINISHED)->where('created_at', '>=', Carbon::today()->subWeek())->min('chance');
        $mlfgame = DB::table('games')->where('chance', $mlfgame)->orderBy('price', 'desc')->first();
        if (!is_null($mlfgame)){
            $u = User::where('id', $mlfgame->winner_id)->first();
            unset($u->password);
            $mlfv = ['user' => $u,'price' => $mlfgame->price,'chance' => self::_getUserChanceOfGame($u, $mlfgame)];
        }
        return $mlfv;
    }
    public function mltd(){
        $u = ['avatar' => '/assets/img/blank.jpg','username' => 'Пока не выбран','steamid64' => ''];
        $mltd = ['user' => $u,'price' => '???','chance' => '???'];
        $mltdgame = DB::table('games')->where('status', Game::STATUS_FINISHED)->where('created_at', '>=', Carbon::today())->min('chance');
        $mltdgame = DB::table('games')->where('chance', $mltdgame)->orderBy('price', 'desc')->first();
        if (!is_null($mltdgame)){
            $u = User::where('id', $mltdgame->winner_id)->first();
            unset($u->password);
            $mltd = ['user' => $u,'price' => $mltdgame->price,'chance' => self::_getUserChanceOfGame($u, $mltdgame)];
        }
        return $mltd;
    }
    
    public function update(){
        $response = [
            'total' => \App\Game::gamesToday(),
            'max' => round(\App\Game::sumFAT()),
            'today' => \App\Game::usersToday(),
            'last' => \App\Game::lastGame(),
            'lw' => $this->lw(),
            'mltd' => $this->mltd(),
            'mlfv' => $this->mlfv()
        ];
        
        $value = (object)$response;
        return response()->json($value);
    }
}