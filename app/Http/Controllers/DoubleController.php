<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Support\Cache;
use App\Http\Controllers\Controller;

use Storage;

class DoubleController extends Controller {
	public function double_index(){
		$gameid = \DB::table('double_games')->max('id');
		if (is_null($gameid)){$this->newGame(); $gameid = \DB::table('double_games')->max('id');}
		$games = \DB::table('double_games')->orderBy('id')->where('id','>=', $gameid-11)->where('id','<', $gameid)->get();
		foreach ($games as $i){ $i->type = $this->_getWinnerType($i->num); } $trp = 0;
		$tr = \DB::table('double_bets')->where('type', 1)->where('game_id', $gameid)->get();
		foreach ($tr as $i){ $trp += $i->price; } $tgp = 0;
		$tg = \DB::table('double_bets')->where('type', 2)->where('game_id', $gameid)->get();
		foreach ($tg as $i){ $tgp += $i->price; } $tbp = 0;
		$tb = \DB::table('double_bets')->where('type', 3)->where('game_id', $gameid)->get();
		foreach ($tb as $i){ $tbp += $i->price; } $mrp = 0;
		$mr = \DB::table('double_bets')->where('type', 1)->where('user_id', $this->user->id)->where('game_id', $gameid)->get();
		foreach ($mr as $i){ $mrp += $i->price; } $mgp = 0;
		$mg = \DB::table('double_bets')->where('type', 2)->where('user_id', $this->user->id)->where('game_id', $gameid)->get();
		foreach ($mg as $i){ $mgp += $i->price; } $mbp = 0;
		$mb = \DB::table('double_bets')->where('type', 3)->where('user_id', $this->user->id)->where('game_id', $gameid)->get();
		foreach ($mb as $i){ $mbp += $i->price; } $users = [];
		$tu = \DB::table('double_bets')->where('game_id', $gameid)/*->groupBy('user_id')*/->get();
		foreach ($tu as $i){ $user = User::where('id', $i->user_id)->first(); $users[$i->user_id] = $user; }
		parent::setTitle('DOUBLE | ');
		$data = [
			'tr'=>$trp,
			'tg'=>$tgp,
			'tb'=>$tbp,
			'mr'=>$mrp,
			'mg'=>$mgp,
			'mb'=>$mbp,
			'rb'=>$trp+$tgp+$tbp
		];
		$am = \DB::table('double_games')->sum('am');
		return view('pages.double', compact('tr','tg','tb','gameid','games','users','data','am'));
	}
	public function getCurrentGame()
    {
        $gameid = \DB::table('double_games')->max('id');
		if (is_null($gameid)){$this->newGame();}
		$gameid = \DB::table('double_games')->max('id');
		$game = \DB::table('double_games')->where('id', $gameid)->first();
        return response()->json($game);
    }
    public function newGame(){
		$gameid = \DB::table('double_games')->insertGetId(['status' => 0]);
		$game = \DB::table('double_games')->where('id', $gameid)->first();
        return response()->json($game);
    }
	public function newBet(Request $request){
		if ($this->user->ban != 0) return response()->json(['success' => false, 'msg' => 'Вы забанены на сайте.']);
        $amount = floor($request->get('amount'));
        $type = $request->get('type');
        $gameid = \DB::table('double_games')->max('id');
        $bets = \DB::table('double_bets')->where('user_id', $this->user->id)->where('game_id', $gameid)->get();
        $thisgame = \DB::table('double_games')->where('id', $gameid)->first();
        if (is_null($thisgame)){ seld::newGame(); $thisgame = \DB::table('double_games')->where('id', $gameid)->first(); }
        if ($thisgame->status > 1) return response()->json(['success' => false, 'msg' => 'Дождитесь следующей игры']);
        if (count($bets)>=5) return response()->json(['success' => false, 'msg' => 'Максимум 5 ставок за игру']);
        if ($amount < 1) return response()->json(['success' => false, 'msg' => 'Минимальная ставка 1 руб.']);
        if(!User::mchange($this->user->id, -$amount)) return response()->json(['success' => false, 'msg' => 'Недостаточно средств']);
        $bet = \DB::table('double_bets')->insertGetId(['user_id' => $this->user->id, 'game_id' => $gameid, 'price' => $amount, 'type' => $type]);
        $user = User::where('id', $this->user->id)->first();
        $bet = \DB::table('double_bets')->where('id', $bet)->first();
        $price = $amount + $thisgame->price;
        if (!$user->is_admin) \DB::table('double_games')->where('id', $gameid)->update(['price' => $price]);	
        $color = $this->_getTypeText($type);
        $realcolor = $this->_getTypeColor($type);
        $tbp = 0;
        $html = view('includes.double_bet', compact('bet','user'))->render();
        $tb = \DB::table('double_bets')->where('type', $type)->where('game_id', $gameid)->get();
        foreach ($tb as $i){ $tbp += $i->price; }
        if ($thisgame->status == 0) \DB::table('double_games')->where('id', $gameid)->update(['status' => 1]);
        $returnValue = [
            'html' => $html,
            'tbp' => $tbp,
            'userid' => $this->user->steamid64,
            'price' => $amount,
            'type' => $type
        ];
        $this->redis->publish('nbdouble', json_encode($returnValue));
        return response()->json(['success' => true, 'msg' => 'Действие выполнено']);
    }

    public function startGame(){
		$order = ['1' => 650, '14' => 725, '2' => 800, '13' => 875, '3' => 950, '12' => 1025, '4' => 1100, '0' => 50, '11' => 125, '5' => 200, '10' => 275, '6' => 350, '9' => 425, '7' => 500, '8' => 575 ];
		$winnerticket = $this->getWinners();
		$scrollpx = $order[$winnerticket];
		$gameid = \DB::table('double_games')->max('id');
		$games = \DB::table('double_games')->orderBy('id')->where('id','>', $gameid-11)->where('id','<=', $gameid)->get();
		$ehtml = '';
		foreach($games as $i){
			if ($this->_getWinnerType($i->num) == 1){
				$ehtml .= '<div class="redg">' . $i->num . '</div>';
			} else if ($this->_getWinnerType($i->num) == 2){
				$ehtml .= '<div class="greeng">' . $i->num . '</div>';
			} else if ($this->_getWinnerType($i->num) == 3){ 
				$ehtml .= '<div class="blackg">' . $i->num . '</div>';
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

    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }

	public function getWinners(){
		$gameid = \DB::table('double_games')->max('id');
		$bets = \DB::table('double_bets')->where('game_id', $gameid)->get();
		$admin = \DB::table('double_admin')->where('id', $gameid)->first();
		$thisgame = \DB::table('double_games')->where('id', $gameid)->first();
		if (!is_null($admin)){
			$winnerticket = $admin->num;
		} else {
			$winnerticket = mt_rand(0, 14);
			if (mt_rand(1, 3) != 1){
				$am = \DB::table('double_games')->sum('am');
				if ($am < 0){
					$trp = 0;
					$tr = \DB::table('double_bets')->where('type', 1)->where('game_id', $gameid)->get();
					foreach ($tr as $i){
						$trp += $i->price;
					}
					$tgp = 0;
					$tg = \DB::table('double_bets')->where('type', 2)->where('game_id', $gameid)->get();
					foreach ($tg as $i){
						$tgp += $i->price;
					}
					$tbp = 0;
					$tb = \DB::table('double_bets')->where('type', 3)->where('game_id', $gameid)->get();
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
		$gameid = \DB::table('double_games')->max('id');
		\DB::table('double_games')->where('id', $gameid)->update(['num' => $winnerticket]);
	    $type = $this->_getWinnerType($winnerticket);
		$winners = \DB::table('double_bets')->where('game_id', $gameid)->where('type', $type)->get();
		$winsum = 0;
		foreach ($winners as $winner){
			if ($type == 1 || $type == 3){
				$user = User::where('id', $winner->user_id)->first();
				$money = $user->money + $winner->price * 2;
				if (!$user->is_admin) $winsum += $winner->price * 2;
				\DB::table('users')->where('id', $winner->user_id)->update(['money' => $money]);
			} else {
				$user = User::where('id', $winner->user_id)->first();
				$money = $user->money + $winner->price * 12;
				if (!$user->is_admin) $winsum += $winner->price * 12;
				\DB::table('users')->where('id', $winner->user_id)->update(['money' => $money]);
			}
		}
		$winsum = $thisgame->price - $winsum;
		\DB::table('double_games')->where('id', $gameid)->update(['num' => $winnerticket , 'am' => $winsum]);
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
		$lastgameid = \DB::table('double_games')->max('id');
		\DB::table('double_games')->where('id',  $lastgameid)->update(['status' =>  $request->get('status')]);
        return $lastgameid;
    }
	public function dwinner(Request $request){
		$gameid = \DB::table('double_games')->max('id');	
		$tec = \DB::table('double_admin')->max('id');
		if($tec == $gameid){
			\DB::table('double_admin')->where('id', '=', $gameid)->update(['num' => $request->get('id')]);
		} else {
			\DB::table('double_admin')->insertGetId(['num' => $request->get('id'), 'id' => $gameid]);	
		}	
		return redirect('/double');
	}

}