<?php

namespace App\Http\Controllers;

use App\Item;
use App\Services\SteamItem;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;

class DiceController extends Controller {
    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
	
    
	public function index(){
        parent::setTitle('КОСТИ | ');
        $gamestats = \DB::table('dice')->orderBy('id', 'desc')->limit(13)->get();
		$games = [];
		foreach ($gamestats as $game){
			$user = User::find($game->user_id);
			$games[] = [
                'avatar' => $user->avatar,
                'username' => $user->username,
                'win' => $game->win
            ];
		}
		return view('pages.dice', compact('games'));
	}
    public function bet(Request $request){
        if ($this->user->ban != 0) return response()->json(['text' => 'Вы забанены на сайте', 'type' => 'error']);
        if ($request->get('sum') == 0) return response()->json(['text' => 'Минимальная ставка 0.01р.', 'type' => 'error']);
        if (!User::mchange($this->user->id, -$request->get('sum'))) return response()->json(['text' => 'У вас недостаточно средств', 'type' => 'error']);
        $roll = rand(1, 6);
        //$am = \DB::table('dice')->sum('am');
        if (rand(0, (ceil($request->get('sum')/10) - 1))!= 0){
            if($request->get('value') == 'low'){
                $roll = rand(4, 6);
            } elseif($request->get('value') == 'high'){
                $roll = rand(1, 3);
            } else {
                while ($roll == $request->get('value')){
                    $roll = rand(1, 6);
                }
            }
        }
        //$roll = 6;
        $am = -$request->get('sum');
        $win = -$request->get('sum');
        if($request->get('value') == 'low') {
            if ($roll < 4){
                User::mchange($this->user->id, $request->get('sum')*2);
                $am += $request->get('sum')*2;
                $win = $request->get('sum')*2;
            }
        } else if($request->get('value') == 'high') {
            if ($roll > 3){
                User::mchange($this->user->id, $request->get('sum')*2);
                $am += $request->get('sum')*2;
                $win = $request->get('sum')*2;
            }
        } else if ($roll == $request->get('value')){
            User::mchange($this->user->id, $request->get('sum')*5);
            $am += $request->get('sum')*6;
            $win = $request->get('sum')*6;
        }
        $returnValue = [
            'avatar' => $this->user->avatar,
            'username' => $this->user->username,
            'win' => $win
        ];
        $this->redis->publish('dice', json_encode($returnValue));
        \DB::table('dice')->insert(['user_id' => $this->user->id, 'money' => $request->get('sum'), 'bet_v' => $request->get('value'), 'value' => $roll, 'am' => $am, 'win' => $win ]);
        return response()->json(['text' => 'Действие выполнено.', 'type' => 'success','value' => $roll]);
    }
}