<?php

namespace App\Http\Controllers;
use Log;
use Cache;
use App\User;
use App\Game;
use App\Bot_bet;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
class VKController extends Controller {
    public function index(Request $request){
        $response = 'ok';
        if($request->get('group_id') == config('mod_vk.group_id')){
            if($request->get('type') == 'confirmation'){
                $response = config('mod_vk.confirmation_key');
            } else if($request->get('secret') == config('mod_vk.secret')){
                switch ($request->get('type')){
                    case 'message_new':
                        self::message_new($request->get('object')['user_id'],$request->get('object')['body']);
                        break;
                }
            }
        }
        return $response;
    }
    private function message_new($user_id, $body){
        if (Cache::has('vk.temp.' . $user_id)){return;} else {Cache::put('vk.temp.' . $user_id, '', 2);}
        $info = self::get_info($user_id);
        if($info['status']){
            $user = $info['info'];
            self::check_cmd($user_id, $body);
        } else{
            self::auth($user_id, $body);
        }
        return;
    }
    private function auth($user_id, $body){
        $data = explode(" ", $body);
        if($data[0]=='/login'){
            if(!isset($data[1])){
                self::send_msg('Укажите реферальный код ↑!', $user_id);
                return;
            }
            if(!isset($data[2])){
                self::send_msg('Укжите первые 4 цифры пароля ↑!', $user_id);
                return;
            }
            $user = User::where('refkode', $data[1])->first();
            if (is_null($user)){
                self::send_msg('Пользователь не найден', $user_id);
                return;
            }
            if (is_null($user->password)){
                self::send_msg('Пароль на сайте не задан', $user_id);
                return;
            }
            if (strlen($user->password) <4){
                self::send_msg('Пароль на сайте слишком короткий (мин 4 символа)', $user_id);
                return;
            }
            if (substr($user->password, 0, 4) != $data[2]){
                self::send_msg('Неверный пароль', $user_id);
                return;
            }
            self::send_msg('Вы успешно авторизировались!<br>ID вк: '.$user_id.'<br>ID на сайте: '.$user->id.'<br>SteamID64: '.$user->steamid64.'<br>Никнейм: '.$user->username.'<br>Profile: https://csgf.ru/user/'.$user->steamid64 , $user_id);
            Cache::forever('vk.user.' . $user_id, ['id' => $user->id, 'steamid64' => $user->steamid64, 'name' => Cache::get('vk.user.name.' . $user_id), 'nick' => $user->username]);
        } else {
            self::send_msg('Вам нужно авторизироваться. Инструкция по авторизации выше ↑!', $user_id);
            return;
        }
    }
    private function get_user($user_id){
        $user_info = json_decode(GameController::curl("https://api.vk.com/method/users.get?user_ids=".$user_id."&v=5.0")); 
        $user_name = $user_info->response[0]->first_name . ' ' . $user_info->response[0]->last_name;
        return $user_name;
    }
    private function get_info($user_id){
        $response = ['status' => false];
        if (Cache::has('vk.user.' . $user_id)){
            $response['info'] = Cache::get('vk.user.' . $user_id);
            $response['status'] = true;
        } else {
            if (Cache::has('vk.user.name.' . $user_id)){
                $user_name = Cache::get('vk.user.name.' . $user_id);
            } else {
                $user_name = self::get_user($user_id);
                Cache::forever('vk.user.name.' . $user_id, $user_name);
                self::send_msg('Доброго времени суток дорогой '.$user_name.'! Вы обращаетесь в поддерджку проекта CSGF.RU! Это ваше первое обращение и вам необходимо авторизироваться! Авторизация необходима для связи с сайтом и удобной работы модераторов. После авторизации вы сможете пообщаться с поддержкой. Для авторизаии пропишите команду:<br>/login KODE PASS<br>Где KODE - ваш реферал, а PASS - первые 4 символа вашего пароля (НЕ ВЕСЬ ПАРОЛЬ! МОДЕРАТОРЫ СВОБОДНО СМОГУТ ПРОЧИТАТЬ ДАННОЕ СООБЩЕНИЕ). Пароль можно установить в вашем профиле! Если у вас не установлен пароль, вы не можете авторизироваться в поддержке.', $user_id);
            }
        }
        return $response;
    }
    
    private function send_msg($mes, $user_id){
        $request_params = array(
            'message' => $mes , 
            'user_id' => $user_id, 
            'access_token' => config('mod_vk.access_token'), 
            'v' => '5.0' 
        ); 
        $get_params = http_build_query($request_params); 
        GameController::curl('https://api.vk.com/method/messages.send?'. $get_params);
        return;
    }
    private function check_cmd($user_id, $body){
        $data = explode(" ", $body);
        switch ($data[0]){
            case '/logout':
                Cache::forget('vk.user.' . $user_id);
                self::send_msg('Вы вышли из аккаунта', $user_id);
                return true;
            default:
                return false;
        }
    }
    private function getDialogs($i,$j){
        $request_params = array(
            'count' => $i,
            'start_message_id' => 50*$j,
            'preview_length' => 1,
            'unread' => 1,
            'access_token' => config('mod_vk.access_token'),
            'v'=>'5.62'
        );
        
        $get_params = http_build_query($request_params); 
        $response = GameController::curl('https://api.vk.com/method/messages.getDialogs?'. $get_params);
        return $response;
    }
    private function getCDialogs(){
        $request_params = array(
            'unread' => 0,
            'access_token' => config('mod_vk.access_token'),
            'v'=>'5.62'
        );
        
        $get_params = http_build_query($request_params); 
        $response = GameController::curl('https://api.vk.com/method/messages.getDialogs?'. $get_params);
        $response = json_decode($response, true);
        $count = $response['response']['count'];
        return $count;
    }
    public function sendTextVK(Request $request){
        $text = $request->get('text');
        $count = self::getCDialogs();
        Log::error($count);
        /*for ($i = 0; $i<ceil($count/50);$i++){
            $dialogs = self::getDialogs(50,$i);
            $dialogs = json_decode($dialogs, true);
            $dialogs = $dialogs['response']['items'];
            foreach( $dialogs as $dialog){
                if(isset($dialog['uid'])){
                    self::send_msg($text, $dialog['uid']);
                    usleep(10000);
                }
            }
            usleep(1000000);
        }*/
        return redirect('/admin');
    }
}
