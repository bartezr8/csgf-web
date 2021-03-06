<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Support\Cache;
use App\Http\Controllers\Controller;
use App\CCentrifugo;
use Storage;
use DB;

class DoubleController extends Controller {
    public function double_index(){
        parent::setTitle('DOUBLE | ');
        $gameid = DB::table('double_games')->max('id');
        if (is_null($gameid)){$this->newGame(); $gameid = DB::table('double_games')->max('id');}
        $games = DB::table('double_games')->orderBy('id','desc')->where('id','>=', $gameid-11)->where('id','<', $gameid)->get();
        foreach ($games as $i){ $i->type = $this->_getWinnerType($i->num); }
        $data = self::getGameInfo($gameid);
        return view('pages.double', compact('gameid','games','data'));
    }
    private function getGameInfo($gameid){
        $trp = 0;$tgp = 0;$tbp = 0;$mrp = 0;$mgp = 0;$mbp = 0;$users = [];
        $bets = DB::table('double_bets')->where('game_id', $gameid)->get();
        foreach($bets as $bet){
            switch ($bet->type) {
                case 1:
                    $trp += $bet->price;
                    if($bet->user_id == $this->user->id) $mrp += $bet->price;
                    break;
                case 2:
                    $tgp += $bet->price;
                    if($bet->user_id == $this->user->id) $mgp += $bet->price;
                    break;
                case 3:
                    $tbp += $bet->price;
                    if($bet->user_id == $this->user->id) $mbp += $bet->price;
                    break;
            }
            $user = User::where('id', $bet->user_id)->first(); 
            $users[$bet->user_id] = $user;
        }
        $data = [
            'tr'=>$trp,
            'tg'=>$tgp,
            'tb'=>$tbp,
            'mr'=>$mrp,
            'mg'=>$mgp,
            'mb'=>$mbp,
            'rb'=>$trp+$tgp+$tbp,
            'bets'=>$bets,
            'users'=>$users
        ];
        return $data;
    }
    public function getCurrentGame()
    {
        $gameid = DB::table('double_games')->max('id');
        if (is_null($gameid)){$this->newGame();}
        $gameid = DB::table('double_games')->max('id');
        $game = DB::table('double_games')->where('id', $gameid)->first();
        return response()->json($game);
    }
    public function newGame(){
        $gameid = DB::table('double_games')->insertGetId(['status' => 0]);
        $game = DB::table('double_games')->where('id', $gameid)->first();
        CCentrifugo::publish('update_p' , ['type' => 'double', 'price' => 0]);       
        return response()->json($game);
    }
    public function newBet(Request $request){
        if ($this->user->ban != 0) return response()->json(['success' => false, 'msg' => 'Вы забанены на сайте.']);
        $amount = round($request->get('amount'),2);$type = $request->get('type');
        $thisgame = DB::table('double_games')->orderBy('id', 'desc')->first();
        if (is_null($thisgame)){ $this->newGame(); $thisgame = DB::table('double_games')->orderBy('id', 'desc')->first(); }
        $gameid = $thisgame->id;
        if ($thisgame->status > 1) return response()->json(['success' => false, 'msg' => 'Дождитесь следующей игры']);
        if ($amount < 0.01) return response()->json(['success' => false, 'msg' => 'Минимальная ставка 0.01 руб.']);
        if(!User::mchange($this->user->id, -$amount)) return response()->json(['success' => false, 'msg' => 'Недостаточно средств']);
        $betid = DB::table('double_bets')->insertGetId(['user_id' => $this->user->id, 'game_id' => $gameid, 'price' => $amount, 'type' => $type]);
        $bet = DB::table('double_bets')->where('id', $betid)->first();
        $price = $amount + $thisgame->price;
        DB::table('double_games')->where('id', $gameid)->update(['price' => $price]); 
        $color = $this->_getTypeText($type);
        $realcolor = $this->_getTypeColor($type);
        $user = $this->user;
        $tbp = 0;$html = view('includes.double_bet', compact('bet','user'))->render();
        $tb = DB::table('double_bets')->where('type', $type)->where('game_id', $gameid)->get();
        foreach ($tb as $i){ $tbp += $i->price; }
        if ($thisgame->status == 0) DB::table('double_games')->where('id', $gameid)->update(['status' => 1]);
        $returnValue = [
            'html' => $html,
            'tbp' => $tbp,
            'userid' => $this->user->steamid64,
            'price' => $amount,
            'type' => $type
        ];
        User::slchange($this->user->id, $amount / 100 * config('mod_game.slimit'));
        $this->redis->publish('nbdouble', json_encode($returnValue));
        CCentrifugo::publish('nbdouble' , $returnValue);
        CCentrifugo::publish('update_p' , ['type' => 'double', 'price' => $price]);       
        return response()->json(['success' => true, 'msg' => 'Действие выполнено']);
    }

    public function startGame(){
        $order = ['1' => 650, '14' => 725, '2' => 800, '13' => 875, '3' => 950, '12' => 1025, '4' => 1100, '0' => 50, '11' => 125, '5' => 200, '10' => 275, '6' => 350, '9' => 425, '7' => 500, '8' => 575 ];
        $winnerticket = $this->getWinners();
        $scrollpx = $order[$winnerticket];
        $gameid = DB::table('double_games')->max('id');
        $games = DB::table('double_games')->orderBy('id')->where('id','>', $gameid-11)->where('id','<=', $gameid)->get();
        $ehtml = '';
        foreach($games as $i){
            if ($this->_getWinnerType($i->num) == 1){
                $ehtml = '<div class="redg">' . $i->num . '</div>' . $ehtml;
            } else if ($this->_getWinnerType($i->num) == 2){
                $ehtml = '<div class="greeng">' . $i->num . '</div>' . $ehtml;
            } else if ($this->_getWinnerType($i->num) == 3){ 
                $ehtml = '<div class="blackg">' . $i->num . '</div>' . $ehtml;
            }
        }
        $returnValue = [
            'ehtml' => $ehtml,
            'win' => $this->_getWinnerType($winnerticket),
            'num' => $winnerticket,
            'scrollpx' => $scrollpx
        ];
        return response()->json($returnValue);
    }
    public function getWinners(){
        $gameid = DB::table('double_games')->max('id');
        $bets = DB::table('double_bets')->where('game_id', $gameid)->get();
        $admin = DB::table('double_admin')->where('id', $gameid)->first();
        $thisgame = DB::table('double_games')->where('id', $gameid)->first();
        if (!is_null($admin)){
            $winnerticket = $admin->num;
        } else {
            $winnerticket = mt_rand(0, 14);
            if (mt_rand(1, 3) != 1){
                $am = DB::table('double_games')->sum('am');
                if ($am < 0){
                    $trp = 0;
                    $tr = DB::table('double_bets')->where('type', 1)->where('game_id', $gameid)->get();
                    foreach ($tr as $i){
                        $trp += $i->price;
                    }
                    $tgp = 0;
                    $tg = DB::table('double_bets')->where('type', 2)->where('game_id', $gameid)->get();
                    foreach ($tg as $i){
                        $tgp += $i->price;
                    }
                    $tbp = 0;
                    $tb = DB::table('double_bets')->where('type', 3)->where('game_id', $gameid)->get();
                    foreach ($tb as $i){
                        $tbp += $i->price;
                    }
                    if ($trp * 2 < $tbp * 2){
                        $winnerticket = mt_rand(1, 7);
                    } else if ($trp * 2 > $tbp * 2){
                        $winnerticket = mt_rand(8, 14);
                    }
                    
                }
            }
        }
        $gameid = DB::table('double_games')->max('id');
        DB::table('double_games')->where('id', $gameid)->update(['num' => $winnerticket]);
        $type = $this->_getWinnerType($winnerticket);
        $winners = DB::table('double_bets')->where('game_id', $gameid)->where('type', $type)->get();
        $winsum = 0;
        foreach ($winners as $winner){
            if ($type == 1 || $type == 3){
                $user = User::where('id', $winner->user_id)->first();
                $money = $user->money + $winner->price * 2;
                if (!$user->is_admin) $winsum += $winner->price * 2;
                DB::table('users')->where('id', $winner->user_id)->update(['money' => $money]);
            } else {
                $user = User::where('id', $winner->user_id)->first();
                $money = $user->money + $winner->price * 12;
                if (!$user->is_admin) $winsum += $winner->price * 12;
                DB::table('users')->where('id', $winner->user_id)->update(['money' => $money]);
            }
        }
        $winsum = $thisgame->price - $winsum;
        DB::table('double_games')->where('id', $gameid)->update(['num' => $winnerticket , 'am' => $winsum]);
        return $winnerticket;
    }
    
    public function _getWinnerType($num){
        if ($num > 0 && $num < 8) $type = 1;
        if ($num == 0) $type = 2;
        if ($num > 7) $type = 3;
        return $type;
    }
    public function _getTypeText($type){
        if ($type == 1) $txt = 'красное';
        if ($type == 2) $txt = 'зеленое';
        if ($type == 3) $txt = 'черное';
        return $txt;
    }
    public function _getTypeColor($type){
        if ($type == 1) $realcolor = '#C9302C';
        if ($type == 2) $realcolor = '#449D44';
        if ($type == 3) $realcolor = '#444444';        
        return $realcolor;
    }
    public function setGameStatus(Request $request){
        $lastgameid = DB::table('double_games')->max('id');
        DB::table('double_games')->where('id',  $lastgameid)->update(['status' =>  $request->get('status')]);
        return $lastgameid;
    }
    public function dwinner(Request $request){
        $gameid = DB::table('double_games')->max('id');    
        $tec = DB::table('double_admin')->max('id');
        if($tec == $gameid){
            DB::table('double_admin')->where('id', '=', $gameid)->update(['num' => $request->get('id')]);
        } else {
            DB::table('double_admin')->insertGetId(['num' => $request->get('id'), 'id' => $gameid]);    
        }    
        return response()->json(['success' => true]);
    }

}