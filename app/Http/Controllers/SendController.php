<?php

namespace App\Http\Controllers;
use App\Bet;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CCentrifugo;
use Storage;

class SendController extends Controller {
    const TITLE_UP = "Перевод | ";
    
    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }
    public function sendlist(Request $request){
        $perevod = \DB::table('perevod')->where('money_id_from', $this->user->steamid64)->orWhere('money_id_to',$this->user->steamid64)->get();
        return json_encode($perevod);
    }
    
    public function send(Request $request){

        $sum = $request->get('sum');
        if($this->user->ban) return response()->json(['text' => 'Вы забанены на сайте!', 'type' => 'error']);
        if($sum=='') return response()->json(['text' => 'Укажите сумму.', 'type' => 'error']);
        if(trim($request->get('steamid')=='')) return response()->json(['text' => 'Укажите получателя.', 'type' => 'error']);
        if($this->user->steamid64 == trim($request->get('steamid'))) return response()->json(['text' => 'Нельзя переводить себе.', 'type' => 'error']);
        if($sum <= 0) return response()->json(['text' => 'Укажите сумму.', 'type' => 'error']);
        $user = \DB::table('users')->where('steamid64', trim($request->get('steamid')))->first();
        if(is_null($user)) return response()->json(['text' => 'Пользователь не найден.', 'type' => 'error']);
        $sum = ceil($sum * 100)/100;
        if(!User::mchange($this->user->id, -$sum)) return response()->json(['text' => 'У вас недостаточно средств.', 'type' => 'error']);
        User::mchange($user->id, $sum);
        \DB::table('perevod')->insert([
            'money_amount' => $sum,
            'money_to' =>  $user->username,
            'money_from' => $this->user->username,
            'money_id_to' => $user->steamid64,
            'money_id_from' => $this->user->steamid64,
        ]);
        $this->_responseMessageToSite('Вам переведены '.$sum.'р. от '.$this->user->username, $user->steamid64);
        return response()->json(['text' => 'Средства переведены.', 'type' => 'success']);
    }
}