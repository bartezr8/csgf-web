<?php

namespace App\Http\Controllers;
use DB;
use Log;
use Auth;
use App\BBet;
use App\BGame;
use App\Services\Item;
use App\User;
use App\Shop;
use App\Item_Steam;
use Carbon\Carbon;
use LRedis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Cache;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CCentrifugo;
use Storage;

class BGameController extends Controller
{
    const SEND_OFFERS_LIST = 'bich.send.offers.list';
    const NEW_BET_CHANNEL = 'bich.newDeposit';
    const BET_DECLINE_CHANNEL = 'bich.depositDecline';
    const INFO_CHANNEL = 'bich.msgChannel';
    const SHOW_WINNERS = 'show.winners';
    const LOG_CHANNEL = 'app_log';
    const QUEUE_CHANNEL = 'queue';
    public $game;

    protected $lastTicket = 0;
    
    public function __construct()
    {
        $this->redis = LRedis::connection();
        parent::__construct();
        $this->game = $this->getLastGame();
        $this->lastTicket = $this->redis->get('bich.last.ticket.' . $this->game->id);
        if(is_null($this->lastTicket)) $this->lastTicket = 0;
    }
    public function  __destruct()
    {
        $this->redis->disconnect();
    }
    public function deposit()
    {
        return redirect(config('mod_bich.trade'));
    }
    public function currentGame()
    {
        $game = BGame::orderBy('id', 'desc')->first();
        if (is_null($game)) $game = $this->newGame();
        $bets = $game->bets()->with(['user', 'game'])->get()->sortByDesc('created_at');
        if (!is_null($this->user)) $user_chance = $this->_getUserChanceOfGame($this->user, $game);
        $chances = json_encode($this->_getChancesOfGame($game));
        if (!is_null($this->user)) $user_items = $this->user->itemsCountByBGame($game);
        parent::setTitle(round($game->price) . ' р. | ');
        return view('pages.bgame', compact('game', 'bets', 'user_chance', 'chances', 'user_items'));
    }
    public function getLastGame()
    {
        $game = BGame::orderBy('id', 'desc')->first();
        if (is_null($game)) $game = $this->newGame();
        return $game;
    }
    public function getCurrentGame()
    {
        $this->game->winner;
        return $this->game;
    }
    public function getWinners()
    {
        $us = $this->game->users();
        $lastBet = BBet::where('b_game_id', $this->game->id)->orderBy('to', 'desc')->first();
        $winTicket = ceil($this->game->rand_number * $lastBet->to);
        $winningBet = BBet::where('b_game_id', $this->game->id)->where('from', '<=', $winTicket)->where('to', '>=', $winTicket)->first();
        
        $this->game->winner_id      = $winningBet->user_id;
        $this->game->price          = $lastBet->to/100;
        $this->game->status         = BGame::STATUS_FINISHED;
        $this->game->finished_at    = Carbon::now();
        $chance = $this->_getUserChanceOfGame($this->game->winner, $this->game);
        $items = $this->sendItems($this->game->bets, $this->game->winner, $chance);
        $this->game->won_items      = json_encode($items['itemsInfo']);
        $this->game->comission      = json_encode($items['commissionItems']);
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
    
    public function sendItems($bets, $user, $chance)
    {
        $itemsInfo = [];
        $userItems = [];
        $items = [];
        $commission = config('mod_game.comission');
        $commissionItems = [];
        $tempPrice = 0;
        $commissionPrice = round(($this->game->price / 100) * $commission);
        foreach ($bets as $bet) {
            $betItems = json_decode($bet->items, true);
            foreach ($betItems as $item) $items[] = $item;
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
                $userItems[] = $item['classid'];
            }
        }
        $value = [
            'appId' => config('mod_game.appid'),
            'steamid' => $user->steamid64,
            'accessToken' => $user->accessToken,
            'items' => $userItems,
            'game' => $this->game->id
        ];
        $this->redis->rpush(self::SEND_OFFERS_LIST, json_encode($value));
        if (config('mod_game.comission_to_shop') && count($commissionItems)) {
            $shopItems = [];
            foreach ($commissionItems as $item) $shopItems[] = $item['classid'];
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
                $this->redis->rpush(self::SEND_OFFERS_LIST, json_encode($valueShop));
            }
        }
        $this->redis->publish(self::LOG_CHANNEL, json_encode('БомжГейм Победил: '. $user->username . ' | Шанс на победу: '.$chance . ' | Комиссия: '.$tempPrice));
        $response = [
            'itemsInfo' => $itemsInfo,
            'commissionItems' => $commissionItems
        ];
        return $response;
    }
   
    public function setPrizeStatus(Request $request)
    {
        $game = BGame::find($request->get('game'));
        $game->status_prize = $request->get('status');
        $game->save();
        return $game;
    }
    public function checkBrokenGames(){
        $games = DB::table('games')->where('status_prize', 2)->get();
        foreach($games as $game){
            $this->fixg($game->id);
        }
    }
    public function fixg($gameid){
        $game = BGame::where('id', $gameid)->first();
        $user = User::find($game['winner_id']);
        $items = json_decode($game->won_items, true);
        foreach ($items as $item) {
            if (isset($item['classid'])) {
                $returnItems[] = $item['classid'];
            }
        }
        $value = [
            'appId' => 730,
            'steamid' => $user->steamid64,
            'accessToken' => $user->accessToken,
            'items' => $returnItems,
            'game' => $gameid
        ];
        $this->redis->rpush(self::SEND_OFFERS_LIST, json_encode($value));
        if ( $game->status != 3) {
            DB::table('games')->where('id', '=', $gameid)->update(['status' => 3]);
        }
    }
    public function newGame(){
        $rand_number = "0.";
        $firstrand = mt_rand(20, 80);
        if (mt_rand(0, config('mod_bich.game_low_chanse')) == 0) $firstrand = mt_rand(3, 96);
        if (mt_rand(0, (config('mod_bich.game_low_chanse') * 2)) == 0) $firstrand = mt_rand(0, 9) . mt_rand(0, 9);
        if(strlen($firstrand) < 2) $firstrand = "0" . $firstrand;
        $rand_number .= $firstrand;
        for($i = 1; $i < 15; $i++) $rand_number .= mt_rand(0, 9);
        $rand_number .= mt_rand(1, 9);
        $game = BGame::create(['rand_number' => $rand_number]);
        $game->hash = md5($game->rand_number);
        $game->rand_number = 0;
        $this->redis->set('current.game', $game->id);
        $this->redis->set('bich.last.ticket.' . $game->id, 0);
        return $game;
    }
    public function checkOffer(Request $request){
        $data = $this->redis->lrange('bich.check.list', 0, -1);
        foreach ($data as $offerJson) {
            $offer = json_decode($offerJson);
            $accountID = $offer->accountid;
            $items = json_decode($offer->items, true);
            $itemsCount = count($items);
            $user = User::where('steamid64', $accountID)->first();
            if (is_null($user)) {
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                continue;
            } else {
                if (empty($user->accessToken)) {
                    $this->redis->lrem('bich.check.list', 0, $offerJson);
                    $this->redis->rpush('bich.decline.list', $offer->offerid);
                    $this->_responseErrorToSite('Введите трейд ссылку!', $accountID, self::BET_DECLINE_CHANNEL);
                    continue;
                }
            }
            if ($user->itemsCountByBGame($this->game) > 0) {
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                $this->_responseErrorToSite('Максимум 1 ставка за игру', $accountID, self::BET_DECLINE_CHANNEL);
            }
            if ($itemsCount > config('mod_bich.max_items')) {
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                $this->_responseErrorToSite('Максимальное кол-во предметов - ' . config('mod_bich.max_items'), $accountID, self::BET_DECLINE_CHANNEL);
            }
            $total_price = $this->_parseItems($items, $missing, $price);
            if ($missing) {
                $this->_responseErrorToSite('Принимаются только предметы из CS:GO', $accountID, self::BET_DECLINE_CHANNEL);
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                continue;
            }
            if ($price) {
                $this->_responseErrorToSite('В вашем трейде есть предметы, цены которых мы не смогли определить', $accountID, self::BET_DECLINE_CHANNEL);
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                continue;
            }
            if ($total_price < config('mod_bich.min_price')) {
                $this->_responseErrorToSite('Минимальная сумма депозита ' . config('mod_bich.min_price') . 'р.', $accountID, self::BET_DECLINE_CHANNEL);
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                continue;
            }
            if ($total_price > config('mod_bich.max_price')) {
                $this->_responseErrorToSite('Максимальная сумма депозита ' . config('mod_bich.max_price') . 'р.', $accountID, self::BET_DECLINE_CHANNEL);
                $this->redis->lrem('bich.check.list', 0, $offerJson);
                $this->redis->rpush('bich.decline.list', $offer->offerid);
                continue;
            }
            $returnValue = [
                'offerid' => $offer->offerid,
                'userid' => $user->id,
                'steamid64' => $user->steamid64,
                'gameid' => $this->game->id,
                'items' => $items,
                'price' => $total_price,
                'success' => true
            ];
            $this->_responseMessageToSite('Обмен №' . $offer->offerid . ' на ' . $total_price . 'р. принимается', $user->steamid64);
            $this->redis->rpush('bich.checked.list', json_encode($returnValue));
            $this->redis->lrem('bich.check.list', 0, $offerJson);
        }
        return response()->json(['success' => true]);
    }
    public function newBet(){
        $data = $this->redis->lrange('bich.bets.list', 0, -1);
        foreach ($data as $newBetJson) {
            $newBet = json_decode($newBetJson, true);
            $user = User::find($newBet['userid']);
            $this->game = $this->getLastGame();
            if (is_null($user)) continue;
            if ($user->ban == 0){
                if ($this->game->id < $newBet['gameid']) continue;
                if ($this->game->id >= $newBet['gameid']) $newBet['gameid'] = $this->game->id;
                if ($this->game->status == BGame::STATUS_PRE_FINISH || $this->game->status == BGame::STATUS_FINISHED) {
                    $this->_responseMessageToSite('Ваша ставка пойдёт на следующую игру.', $user->steamid64);
                    $this->redis->lrem('bich.bets.list', 0, $newBetJson);
                    $newBet['gameid'] = $newBet['gameid'] + 1;
                    $this->redis->rpush('bich.bets.list', json_encode($newBet));
                    continue;
                }
                $this->redis->lrem('bich.bets.list', 0, $newBetJson);
                $totalItems = $user->itemsCountByBGame($this->game);

                if(count($newBet['items']) == 0) continue;
                $this->lastTicket = $this->redis->get('bich.last.ticket.' . $this->game->id);
                $ticketFrom = $this->lastTicket + 1;
                $ticketTo = $ticketFrom + ($newBet['price'] * 100) - 1;
                $bet = new BBet();
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

                User::slchange($user->id, $newBet['price'] / 100  * config('mod_game.slimit'));

                $bets = BBet::where('b_game_id', $this->game->id)->get();
                $this->game->items = $bets->sum('itemsCount');
                $this->game->price = $bets->sum('price');

                if (count($this->game->users()) == 1) {
                    $this->game->status = BGame::STATUS_PLAYING;
                    $this->game->started_at = Carbon::now();
                }
                if (count($this->game->users()) == 3) {
                    $this->game->status = BGame::STATUS_FINISHED;
                    $this->redis->publish(self::SHOW_WINNERS, true);
                }
                
                $this->game->save();
                
                $this->redis->publish(self::LOG_CHANNEL, json_encode('БичСтавка: '.$newBet['price'].' р. | '.$user->username));
                $returnValue = [
                    'betId' => $bet->id,
                    'userId' => $user->steam64,
                    'html' => view('includes.bbet', compact('bet'))->render(),
                    'itemsCount' => $this->game->items,
                    'gamePrice' => $this->game->price,
                    'gameStatus' => $this->game->status,
                    'userplace' => $this->game->users(),
                    'betprice' => $newBet['price']
                ];
                $this->redis->publish(self::NEW_BET_CHANNEL, json_encode($returnValue));
            } else {
                $this->redis->lrem('bich.bets.list', 0, $newBetJson);
                $this->_responseMessageToSite('Вы забанены на сайте.', $user->steamid64);
            }
        }
        return $this->_responseSuccess();
    }
    public static function _getChancesOfGame($game)
    {
        $chances = [];
        foreach ($game->users() as $user) {
            $vip = false;
            if (strpos(strtolower(' '.$user->username),  strtolower(config('app.sitename'))) != false) $vip = true;
            $chances[] = [
                'chance' => self::_getUserChanceOfGame($user, $game),
                'items' => User::find($user->id)->itemsCountByBGame($game),
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
    private function _responseErrorToSite($message, $user, $channel)
    {
        return $this->redis->publish($channel, json_encode([
            'user' => $user,
            'msg' => $message
        ]));
    }
    public static function _getUserChanceOfGame($user, $game)
    {
        $chance = 0;
        if (!is_null($user)) {
            if ($user->steamid64 != config('mod_game.bonus_bot_steamid64')) {
                $bet = BBet::where('b_game_id', $game->id)
                    ->where('user_id', $user->id)
                    ->sum('price');
                if ($game->price > 0 && $bet) $chance = round($bet / $game->price, 3) * 100; 
            } else {
                $chance = 0;
            }
        }
        return $chance;
    }

    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }
    private function _responseSuccess(){
        return response()->json(['success' => true]);
    }
}