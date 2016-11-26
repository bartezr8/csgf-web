<?php

namespace App\Http\Controllers;
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
        $bet_sum = $request->get('sum');
        if ($this->user->ban != 0) return response()->json(['text' => 'Вы забанены на сайте', 'type' => 'error']);
        if ($bet_sum == 0) return response()->json(['text' => 'Минимальная ставка 0.01р.', 'type' => 'error']);
        if (!User::mchange($this->user->id, -$bet_sum)) return response()->json(['text' => 'У вас недостаточно средств', 'type' => 'error']);
        $roll = rand(1, 6);
        $am = \DB::table('dice')->sum('am') + $bet_sum;
        if ((rand(0, floor($bet_sum/25))!= 0) || ($am > 0) ){
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
        $am = -$bet_sum;
        $win = -$bet_sum;
        if($request->get('value') == 'low') {
            if ($roll < 4){
                User::mchange($this->user->id, $bet_sum*2);
                $am += $bet_sum*2;
                $win = $bet_sum*2;
            }
        } else if($request->get('value') == 'high') {
            if ($roll > 3){
                User::mchange($this->user->id, $bet_sum*2);
                $am += $bet_sum*2;
                $win = $bet_sum*2;
            }
        } else if ($roll == $request->get('value')){
            User::mchange($this->user->id, $bet_sum*5);
            $am += $bet_sum*6;
            $win = $bet_sum*6;
        }
        $returnValue = [
            'avatar' => $this->user->avatar,
            'username' => $this->user->username,
            'win' => $win
        ];
        $this->redis->publish('dice', json_encode($returnValue));
        \DB::table('dice')->insert(['user_id' => $this->user->id, 'money' => $bet_sum, 'bet_v' => $request->get('value'), 'value' => $roll, 'am' => $am, 'win' => $win ]);
        return response()->json(['text' => 'Действие выполнено.', 'type' => 'success','value' => $roll]);
    }
}