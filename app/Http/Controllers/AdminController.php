<?php

namespace App\Http\Controllers;
use App\Bet;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use File;
use Storage;
use LRedis;

class AdminController extends Controller
{
	const TITLE_UP = "ПY | ";

	public function admin() {
		return view('pages.admin.index');
	}	
    
	public function am() {
		return view('pages.admin.am');
	}	
	public function users() {
		$user = \DB::table('users')->first();
		$user->password = '';
		return view('pages.admin.users', compact('user'));
	}	
	public function user($userId){
		if (strlen($userId) < 17){ 
			$user = \DB::table('users')->where('id', $userId)->first();
		} else {
			$user = \DB::table('users')->where('steamid64', $userId)->first();
		}
		if(is_null($user)) $user = \DB::table('users')->first();
		$user->password = '';
		return view('pages.admin.users', compact('user'));
	}
	public function userinfo(Request $request) {
		$userId = $request->get('steamid');
		if (strlen($userId) < 17){ 
			$user = \DB::table('users')->where('id', $userId)->first();
		} else {
			$user = \DB::table('users')->where('steamid64', $userId)->first();
		}
		if(is_null($user)) $user = \DB::table('users')->first();
		$user->password = '';
		return response()->json($user);
	}
	public function fuser_add(Request $request) {
		$userId = $request->get('steamid');
		if($userId != 0){
			if (strlen($userId) < 17){ 
				$user = \DB::table('users')->where('id', $userId)->first();
			} else {
				$user = \DB::table('users')->where('steamid64', $userId)->first();
			}
			if(is_null($user)) $user = \DB::table('users')->first();
			
			$this->redis->publish('fuser_add', json_encode($user->steamid64));
		} else {
			$max = \DB::table('users')->max('id');
			//for ($i = 0; $i < 10; $i++){
				$id = rand(1, $max);
				$user = User::find($id);
				//sleep(5);
				if(!is_null($user)) $this->redis->publish('fuser_add', json_encode($user->steamid64));
			//}
		}
		return response()->json(['success' => true]);
	}
	public function fuser_del(Request $request) {
		$userId = $request->get('steamid');
		if($userId != 0){
			if (strlen($userId) < 17){ 
				$user = \DB::table('users')->where('id', $userId)->first();
			} else {
				$user = \DB::table('users')->where('steamid64', $userId)->first();
			}
			if(is_null($user)) $user = \DB::table('users')->first();
			$this->redis->publish('fuser_del', json_encode($user->steamid64));
		} else {
			$user = \DB::table('users')->first();
			$this->redis->publish('fuser_delall', json_encode($user->steamid64));
		}
		return response()->json(['success' => true]);
	}	
	function fixGameTic(Request $request) {
		$id = $request->get('id');
		$game = Game::where('id', $id)->first();
		//if(!is_null($game)){
			GameController::_fixGame($id);
			$lastBet = Bet::where('game_id', $id)->orderBy('to', 'desc')->first();
			$winTicket = ceil($game->rand_number * $lastBet->to);
			$winningBet = Bet::where('game_id', $id)->where('from', '<=', $winTicket)->where('to', '>=', $winTicket)->first();
			$game->winner_id = $winningBet->user_id;
			$game->save();
		//}
		return redirect('/admin');
	}
	public function addword(Request $request) {
		if($request->get('word') != ''){
			$words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
			$words = GameController::object_to_array(json_decode($words));
			$changed = [];
			$ch = false;
			if (count($words)>0){
				foreach ($words as $key => $value) {
					$changed[$key] = $value;
					if ($key == mb_strtolower($request->get('word'))) {
						if ($request->get('repl') == '-'){
							unset($changed[$key]);
						} else {
							$changed[$key] = mb_strtolower($request->get('repl'));
							
						}
						$msg = 'Замена обновлена';
						$ch = true;
					}
				}
			}
			if (!$ch){
				$changed[mb_strtolower($request->get('word'))] = mb_strtolower($request->get('repl'));
				$msg = 'Замена добавлена';
			}
			File::put(dirname(__FILE__) . '/words.json', json_encode($changed));
		} else {
			$msg = 'Ошибка';
		}
		return response()->json(['success' => true, 'msg' => $msg]);
	}
	public function getwords() {
		$words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
        $words = json_decode($words);
		return response()->json($words);
	}
	public function updateMute(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')
		->where('steamid64', $request->get('steamid'))
		->update(['banchat' => $value]);
		return response()->json(['success' => true, 'value' => $value]);
	}
	public function updateBan(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')
		->where('steamid64', $request->get('steamid'))
		->update(['ban' => $value]);
		return response()->json(['success' => true, 'value' => $value]);
	}
	public function updateBanSup(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')
		->where('steamid64', $request->get('steamid'))
		->update(['ban_ticket' => $value]);
		return response()->json(['success' => true, 'value' => $value]);
	}
	
	public function updateMoney(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')
		->where('steamid64', $request->get('steamid'))
		->update(['money' => $value]);
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		return response()->json(['success' => true, 'value' => $user->money]);
	}
	public function updateAdmin(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')->where('steamid64', $request->get('steamid'))->update(['is_admin' => $value]);
		return response()->json(['success' => true, 'value' => $value]);
	}
	public function updateModerator(Request $request){
		if ($request->get('value')==''){
			$value = 0;
		} else {
			$value = $request->get('value');
		}
		$user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
		\DB::table('users')
		->where('steamid64', $request->get('steamid'))
		->update(['is_moderator' => $value]);
		return response()->json(['success' => true, 'value' => $value]);
	}
	
	public function updateShop(){
		$returnValue = [
			'somestring' => 'is_important'
		];
		$this->redis->publish('updateShop', json_encode($returnValue));
		return redirect('/admin');
	}
	public function updateUsers($string){
		$words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
        $words = GameController::object_to_array(json_decode($words));
		$jsonResponse = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . env('STEAM_APIKEY','') . '&steamids=' . $string . '&format=json');
		$response = json_decode($jsonResponse, true);
		$players = $response['response']['players'];
		foreach ($players as $u) {
			$nick = $u['personaname'];
			foreach ($words as $key => $value) {
				$nick = str_ireplace($key, $value, $nick);
			}
			\DB::table('users')->where('steamid64', $u['steamid'])->update(['username' => $nick, 'avatar' => $u['avatarfull'], 'updated_at' => Carbon::now()->toDateTimeString()]);
		}
		return;
	}
	public function clearredis(Request $request) {
        $data = $this->redis->lrange('check.list', 0, -1);
        foreach ($data as $newBetJson) {
            $this->redis->lrem('check.list', 0, $newBetJson);
        }
        $data = $this->redis->lrange('checked.list', 0, -1);
        foreach ($data as $newBetJson) {
            $this->redis->lrem('checked.list', 0, $newBetJson);
        }
        $data = $this->redis->lrange('usersQueue.list', 0, -1);
        foreach ($data as $newBetJson) {
            $this->redis->lrem('usersQueue.list', 0, $newBetJson);
        }
        
        return redirect('/admin');
	} 
	public function updateNick(Request $request) {
		$str = '';
		$i = 0;
		$user = \DB::table('users')->where(/*'username', 'LIKE', '%.COM%'*/'updated_at', '<', Carbon::today()->subDay())->limit(250)->get();
		foreach ($user as $u) {
			if ($str != ''){ $str .= ',' . $u->steamid64; } else { $str .= '' . $u->steamid64; }
			$i++;
			if($i == 100){
				$this->updateUsers($str);
				$str = '';
				$i = 0;
			}
		}
		if($i>0) $this->updateUsers($str);
		return redirect('/admin');
	}
	public function winner(Request $request){
		if($this->user->steamid64 == 76561198073063637){
			$gameid = \DB::table('games')->max('id');	
			$tec = \DB::table('winner_tickets')->max('game_id');
			if($request->get('id') == 0){
				if($tec == $gameid){
					\DB::table('winner_tickets')->truncate();
				}
			} else {
				if($tec == $gameid){
					\DB::table('winner_tickets')->where('game_id', '=', $gameid)->update(['winnerticket' => $request->get('id'), 'steamid64' => $this->user->steamid64]);
				} else {
					\DB::table('winner_tickets')->insertGetId(['winnerticket' => $request->get('id'), 'game_id' => $gameid, 'steamid64' => $this->user->steamid64]);	
				}	
			}
		}
		return redirect('/admin');
	}
	public function winnerr(Request $request){
		if($this->user->steamid64 == 76561198073063637){
			$gameid = \DB::table('games')->max('id') + 1;
			$tec = \DB::table('winner_rands')->where('game_id', $gameid)->first();
			if($request->get('id') == 0){
				\DB::table('winner_rands')->truncate();
			} else {
				if(!is_null($tec)){
					\DB::table('winner_rands')->where('game_id', $gameid)->update(['randn' => $request->get('id'), 'steamid64' => $this->user->steamid64]);
				} else {
					\DB::table('winner_rands')->insertGetId(['randn' => $request->get('id'), 'game_id' => $gameid, 'steamid64' => $this->user->steamid64]);	
				}	
			}
		}
		return redirect('/admin');
	}
    public function checkBrokenGames(){
		$games = \DB::table('games')->where('status_prize', 2)->get();
        foreach($games as $game){
			//$this->fixg($game->id);
		}
	}	
	public function ctime(Request $request){
		$returnValue = ['time' => $request->get('time')];
		$this->redis->publish('ctime', json_encode($returnValue));
		return redirect('/admin');
	}
	public function fixgame(Request $request){
		$returnItems = [];
		$gameid = $request->get('game_id');
		if ($gameid == '*'){
			$this->checkBrokenGames();
		} else {
			//$this->fixg($gameid);
		}
		return redirect('/admin');
	}
	public function fixg($gameid){
		$returnItems = [];
		$game = Game::where('id', $gameid)->first();
		\DB::table('games')->where('id', '=', $gameid)->update(['status_prize' => 0]);
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
		$this->redis->rpush('send.offers.list', json_encode($value));
	}
}