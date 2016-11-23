<?php

namespace App\Http\Controllers;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;

class GiveOutController extends Controller {
	public function out_index(){
		$OUTS = \DB::table('giveouts')->where('status', '>', 0)->where('status', '<', 3)->orderBy('id', 'desc')->limit(16)->get();
		$avatars = [];
		foreach ($OUTS as $out){
			$user = User::find($out->user_id);
			$avatars[] = $user->avatar;
		}
		return view('pages.out', compact('avatars'));
	}
    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
	public function startOut(){
		if (\Cache::has('out.user.' . $this->user->id)) return response()->json(['text' => 'Подождите...', 'type' => 'error']);
		\Cache::put('out.user.' . $this->user->id, '', 1);
		if (strpos(strtolower($this->user->username),  strtolower(config('app.sitename'))) != false){
			$currout = \DB::table('giveouts')->where('user_id', $this->user->id)->where('status', '<', 2)->first();
			if(!is_null($currout)){
				return response()->json(['text' => 'Вы уже участвуете в раздаче.', 'type' => 'error']);
			} else {
				$id = \DB::table('giveouts')->insertGetId([
					'user_id' => $this->user->id,
					'status' => 0,
					'price' => config('mod_out.outmoney'),
					'date' => Carbon::now()->toDateTimeString()
				]);
				return response()->json(['text' => 'Действие выполнено.', 'type' => 'success']);
			}
		} else return response()->json(['text' => 'Ваш ник должен содержать ' . config('app.sitename') , 'type' => 'error']);
		
	}
	public function getOut(){
		if (\Cache::has('out.user.' . $this->user->id)) return;
		\Cache::put('out.user.' . $this->user->id, '', 1);
		$currout = \DB::table('giveouts')->where('user_id', $this->user->id)->where('status', '<', 2)->first();
		$sumout = \DB::table('giveouts')->where('user_id', $this->user->id)->where('status', 2)->sum('price');
		if(is_null($currout)){
			return response()->json(['do' => 'false', 'sum' => $sumout, 'thisMon' => '0.00']);
		} else {
			if((Carbon::parse($currout->date)->timestamp + config('mod_out.outtime')) < Carbon::now()->timestamp){
				if($currout->status == 0 ){
					\DB::table('giveouts')->where('id', $currout->id)->update(['status' => 1]);
					$this->redis->publish('out_new', $this->user->avatar);
				}
				$currout->status = 1;
				$currout->left = (Carbon::parse($currout->date)->timestamp + config('mod_out.outtime') - Carbon::now()->timestamp);
				return response()->json(['do' => 'true', 'val' => $currout, 'sum' => $sumout, 'thisMon' => config('mod_out.outmoney')]);
			} else {
				$currout->left = (Carbon::parse($currout->date)->timestamp + config('mod_out.outtime') - Carbon::now()->timestamp);
				return response()->json(['do' => 'true', 'val' => $currout, 'sum' => $sumout, 'thisMon' => '0.00']);
			}
		}
		
	}	
	public function getMon(){
		if (\Cache::has('out.user.' . $this->user->id)) return response()->json(['text' => 'Подождите...', 'type' => 'error']);
		\Cache::put('out.user.' . $this->user->id, '', 1);
		$currout = \DB::table('giveouts')->where('user_id', $this->user->id)->where('status', 1)->first();
		if(is_null($currout)){
			return response()->json(['text' => 'Нечего забирать.', 'type' => 'error']);
		} else {
			\DB::table('giveouts')->where('id', $currout->id)->update(['status' => 2]);
			$this->user->money += config('mod_out.outmoney');
			$this->user->save();
			return response()->json(['text' => 'Средства зачислены.', 'type' => 'success']);
		}
		
	}
	public function checkUsers(){
		$outs = \DB::table('giveouts')->where('status', 0)->get();
 		$str = '';
		$i = 0;
		foreach($outs as $out){
			$u = User::find($out->user_id);
			if ($str != ''){ $str .= ',' . $u->steamid64; } else { $str .= '' . $u->steamid64; }
			$i++;
			if($i == 100){
				$this->updateUsers($str);
				$str = '';
				$i = 0;
			}
		}
		if($i>0) $this->updateUsers($str);
		
		foreach($outs as $out){
			$u = User::find($out->user_id);
			if (strpos(strtolower($u->username),  strtolower(config('app.sitename'))) != false){
				if((Carbon::parse($out->date)->timestamp + config('mod_out.outtime')) < Carbon::now()->timestamp){
					if($out->status == 0)\DB::table('giveouts')->where('id', $out->id)->update(['status' => 1]);
					self::_responseMessageToSite('Вы победили в раздаче, заберите приз', $u->steamid64);
					$this->redis->publish('out_new', $this->user);
				}
			} else {
				\DB::table('giveouts')->where('id', $out->id)->update(['status' => 3]);
				self::_responseMessageToSite('Ваша раздача аннулирована (Вы сменили ник)', $u->steamid64);
			}
		}
		
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
			\DB::table('users')->where('steamid64', $u['steamid'])->update(['username' => $nick, 'avatar' => $u['avatarfull']]);
		}
		return;
	}
	
}