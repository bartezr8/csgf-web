<?php

namespace App\Http\Controllers;
use DB;
use App\Bet;
use App\Game;
use App\Services\Item;
use App\User;
use App\Item_BP;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class PagesController extends Controller
{
    public function support()
    {
        parent::setTitle('Поддержка | ');

        return view('pages.support');
    }
    
    public function fairplay($gameID)
    {
        parent::setTitle('Честная игра | ');

        $game = Game::with(['winner'])->where('status', Game::STATUS_FINISHED)->where('id', $gameID)->first();
        if(!is_null($game)){
            $betid = DB::table('bets')->where('game_id', $game->id)->max('id');
            $bet = Bet::where('id', $betid)->first();
            return view('pages.fairplay', compact('game', 'bet'));
        }
        return view('pages.fairplay');
    }

    public function fairplay_no()
    {
        parent::setTitle('Честная игра | ');

        return view('pages.fairplay');
    }

    public function about()
    {
        parent::setTitle('О сайте | ');

        return view('pages.about');
    }

    public function top()
    {
        parent::setTitle('Топ | ');
        $userst = DB::table('users')
            ->select('users.id',
                'users.username',
                'users.avatar',
                'users.steamid64',
                DB::raw('SUM(games.price) as top_value'),
                DB::raw('COUNT(games.id) as wins_count')
            )
            ->join('games', 'games.winner_id', '=', 'users.id')
            ->groupBy('users.id')
            ->orderBy('top_value', 'desc')
            ->limit(10)
            ->get();
        
        $tplace = 1;
        $i = 0;
        foreach($userst as $u){
            $userst[$i]->wins_count = count(DB::table('games')
                ->where('chance', '<', 100)
                ->where('winner_id', $u->id)
                ->get());
            $userst[$i]->games_played = count(DB::table('games')
                ->where('chance', '<', 100)
                ->join('bets', 'games.id', '=', 'bets.game_id')
                ->where('bets.user_id', $u->id)
                ->groupBy('bets.game_id')
                ->select('bets.id')->get());
            $userst[$i]->win_rate = 0;
            if($userst[$i]->games_played > 0) $userst[$i]->win_rate = round( $userst[$i]->wins_count / $userst[$i]->games_played, 3) * 100;
            $i++;
        }
        
        $users = DB::table('users')
            ->select('users.id',
                'users.username',
                'users.avatar',
                'users.steamid64',
                DB::raw('SUM(games.price) as top_value'),
                DB::raw('COUNT(games.id) as wins_count')
            )
            ->join('games', 'games.winner_id', '=', 'users.id')
            ->where('games.created_at', '>=', Carbon::today()->subWeek())
            ->groupBy('users.id')
            ->orderBy('top_value', 'desc')
            ->limit(10)
            ->get();
        
        $place = 1;
        $i = 0;
        foreach($users as $u){
            $users[$i]->wins_count = count(DB::table('games')
                ->where('created_at', '>=', Carbon::today()->subWeek())
                ->where('chance', '<', 100)
                ->where('winner_id', $u->id)
                ->get());
            $users[$i]->games_played = count(DB::table('games')
                ->where('chance', '<', 100)
                ->where('games.created_at', '>=', Carbon::today()->subWeek())
                ->join('bets', 'games.id', '=', 'bets.game_id')
                ->where('bets.user_id', $u->id)
                ->groupBy('bets.game_id')
                ->select('bets.id')->get());
            $users[$i]->win_rate = 0;
            if($users[$i]->games_played > 0) $users[$i]->win_rate = round( $users[$i]->wins_count / $users[$i]->games_played, 3) * 100;
            $i++;
        }
        $referals = DB::table('users')
            ->groupBy('users.id')
            ->orderBy('refcount', 'desc')
            ->limit(10)
            ->get();
        $refplace = 1;
        
        $gouts = DB::table('users')
            ->select('users.id',
                'users.username',
                'users.avatar',
                'users.steamid64',
                DB::raw('SUM(giveouts.price) as top_value'),
                DB::raw('COUNT(giveouts.id) as count')
            )
            ->join('giveouts', 'giveouts.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->orderBy('top_value', 'desc')
            ->limit(10)
            ->get();
        $outplace = 1;
        
        return view('pages.top', compact('users', 'place', 'userst', 'tplace', 'refplace', 'referals', 'outplace', 'gouts'));
    }

    public function user($userId)
    {
        $user = User::where('steamid64', $userId)->first();
        if(!is_null($user)) {
            $games = Game::where('winner_id', $user->id)->get();
            $winz = $games->count();
            $gamesList = [];
            $gamesList2 = [];
            $gamecount = 0;
            $wins = 0;
            if($user->steamid64 != '76561197960265728'){
                $gamesPlayed = DB::table('games')
                    ->join('bets', 'games.id', '=', 'bets.game_id')
                    ->where('bets.user_id', $user->id)
                    ->where('games.status', 3)
                    ->groupBy('bets.game_id')
                    ->orderBy('games.created_at', 'desc')
                    ->select('games.*', DB::raw('SUM(bets.price) as betValue'))->get();
            
                $i = 0;
                foreach ($gamesPlayed as $game) {
                    $gamesList[$i] = (object)[];
                    $gamesList[$i]->id = $game->id;
                    $gamesList[$i]->win = false;
                    $gamesList[$i]->bank = $game->price;
                    if ($game->status != Game::STATUS_FINISHED) $gamesList[$i]->win = -1;
                    if ($game->winner_id == $user->id) $gamesList[$i]->win = true;
                    if ($userId != config('mod_game.bot_steamid')){
                        $gamesList[$i]->chance = round($game->betValue / $game->price, 3) * 100;
                        if ($gamesList[$i]->win) $wins++;
                        $gamecount++;
                    } else {
                        $gamesList[$i]->chance = 0;
                    }
                    if($i<100)$gamesList2[$i] = $gamesList[$i];
                    $i++;
                }
            } else {
                $gamesPlayed = NULL;
            }
            
            $username = $user->username;
            $avatar = $user->avatar;
            $totalBank = $games->sum('price');
            $slimit = $user->slimit;
            $slimit_ = '';
            while($slimit>1000){
                $slimit = round($slimit/1000,1);
                $slimit_ .='к';
            }
            $slimit .= $slimit_;
            $url = 'https://steamcommunity.com/profiles/' . $user->steamid64 . '/';
            if ( $gamecount > 0 ){
                $winrate = count($gamesPlayed) ? round($wins / $gamecount, 3) * 100 : 0;
            } else {
                $winrate = 0;
            }
            $wins = $winz;
            $games = $gamecount;
            $list = $gamesList2;
            $tradeurl = $user->trade_link;

            
            parent::setTitle($username.' | ');
        }
        else
        {
            return redirect()->route('index');
        }

        return view('pages.user', compact('username', 'avatar', 'slimit', 'wins', 'url' , 'winrate' , 'totalBank' , 'games', 'list', 'userId', 'tradeurl'));
    }

    public function settings()
    {
        parent::setTitle('Настройки | ');
        
        return view('pages.settings');
    }

    public function history()
    {
        parent::setTitle('История игр | ');

        $games = Game::with(['bets', 'winner'])->where('status', Game::STATUS_FINISHED)->orderBy('created_at', 'desc')->simplePaginate(20);
        return view('pages.history', compact('games'));
    }
    
    public function dep()
    {
        parent::setTitle('Депозит | ');
        return view('pages.deposit', compact('games'));
    }

    public function escrow()
    {
        parent::setTitle('ESCROW | ');

        return view('pages.escrow');
    }
    public function success(){
        return redirect()->route('index');
    }
    public function fail(){
        return redirect()->route('index');
    }
    public function game($gameId){
        if(isset($gameId) && Game::where('status', Game::STATUS_FINISHED)->where('id', $gameId)->count()){
            $game = Game::with(['winner'])->where('status', Game::STATUS_FINISHED)->where('id', $gameId)->first();
            $betid = DB::table('bets')->where('game_id', $game->id)->max('id');
            $game->ticket = ceil($game->rand_number * ($game->price * 100));
            $bets = $game->bets()->with(['user','game'])->get()->sortByDesc('created_at');
            $chances = json_encode(GameController::_getChancesOfGame($game));
            $citems = array_values(json_decode($game->comission, true));
            $bedz = [];
            foreach ($bets as $bet){
                $items = array_values(json_decode($bet->items, true));
                foreach ($citems as $ckey => $i ) {
                    if (in_array($i, $items)){
                        $key = array_search($i, $items);
                        $citems[$ckey] = '';
                        $items[$key]['com'] = true;
                    }
                }
                $bet->items = json_encode($items);
                $bedz[] = $bet;
                if (count($citems) == 0) continue;
            }
            $bets = $bedz;

            parent::setTitle('Игра #'.$gameId.' | ');

            return view('pages.game', compact('game', 'bets', 'chances'));
        }
        return redirect()->route('index');
    }
    public function myinventory(Request $request)
    {
        parent::setTitle('Мой инвентарь | ');

        if($request->getMethod() == 'GET'){
            return view('pages.myinventory', compact('title'));
        } else {
            $response = ['success' => false];
            if(!\Cache::has('inventory_' . $this->user->steamid64)) {
                $jsonInventory = GameController::curl('http://steamcommunity.com/inventory/' . $this->user->steamid64 . '/730/2?l=russian&count=1000');
                $items = json_decode($jsonInventory, true);
                $descriptions = [];$inventory = [];
                if(isset($items['assets']) && isset($items['descriptions']) && isset($items['success'])){
                    if ($items['success'] == 1) {
                        foreach ($items['descriptions'] as $id => $value) {
                            $class_instance = $value['classid'].'_'.$value['instanceid'];
                            $descriptions[$class_instance] = $value;
                        }
                        foreach ($items['assets'] as $id => $value) {
                            $class_instance = $value['classid'].'_'.$value['instanceid'];
                            $item = $descriptions[$class_instance];
                            
                            $info = new Item($item);
                            if(!Item::pchk($info)){
                                $info->price = 0;
                            }
                            $item['price'] = $info->price;
                            $item['assetid'] = $value['assetid'];
                            if($item['price']>0) $inventory[] = $item;
                        }
                        $response = [
                            'success' => true,
                            'items' => $inventory
                        ];
                        \Cache::put('inventory_' . $this->user->steamid64, $response, 60);
                    }
                }
            } else {
                $response = \Cache::get('inventory_' . $this->user->steamid64);
            }
            return response()->json($response);
        }
    }
    public function pay(Request $request)
    {
        $amount = $request->get('sum');
        if (!$amount) $amount = 1;
        if ($amount<1) $amount = 1;
        $user = $this->user;
        $id = DB::table('freekassa_payments')->insertGetId(['account' => $user->id, 'AMOUNT' => $amount, 'dateCreate' => Carbon::now()->toDateTimeString(), 'status' => 0 ]);
        $hash = md5(config('pay.freekassa_id').':'.$amount.':'.config('pay.freekassa_s1').':'.$id);
        header('Location: https://www.free-kassa.ru/merchant/cash.php?m='.config('pay.freekassa_id').'&oa='.$amount.'&o='.$id.'&s='.$hash);
        exit();
    }   
    public function rand_url()
    {
        $urls = config('mod_game.urls');
        $rand = rand(0, (count($urls) - 1));
        $url = 'https://yandex.ru/search/?text='.urlencode($urls[$rand]);
        header('Location: '.$url);
        exit();
    }
}