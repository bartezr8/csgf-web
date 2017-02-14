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
                        self::message_new($request->get('object')['user_id'],$request->get('object')['body'],$request->get('object')['id']);
                        break;
                }
            }
        }
        return $response;
    }
    private function message_new($user_id, $body, $mes_id){
        if (Cache::has('vk.temp.' . $user_id)){return;} else {Cache::put('vk.temp.' . $user_id, '', 2);}
        $info = self::get_info($user_id);
        if($info['status']){
            $user = $info['info'];
            self::check_cmd($user_id, $body, $mes_id);
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
                self::send_msg('Укжите пароль ↑!', $user_id);
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
            if ($user->password != $data[2]){
                self::send_msg('Неверный пароль', $user_id);
                return;
            }
            $inital = self::send_msg('Вы успешно авторизировались!<br>ID вк: '.$user_id.'<br>ID на сайте: '.$user->id.'<br>SteamID64: '.$user->steamid64.'<br>Никнейм: '.$user->username.'<br>Profile: https://csgf.ru/user/'.$user->steamid64 , $user_id);
            Cache::forever('vk.user.' . $user_id, ['id' => $user->id, 'steamid64' => $user->steamid64, 'init' => $inital, 'name' => Cache::get('vk.user.name.' . $user_id), 'nick' => $user->username]);
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
                self::send_msg($user_name.'! Вы обращаетесь в поддерджку проекта CSGF.RU! Это ваше первое обращение и вам необходимо авторизироваться! Для авторизаии пропишите команду:<br>/login РЕФЕРАЛ ПАРОЛЬ<br>Где РЕФЕРАЛ - ваш реферал установленный на сайте, а ПАРОЛЬ - ваш пароль. Пароль можно установить в вашем профиле! Если у вас не установлен пароль, вы не можете авторизироваться в поддержке.', $user_id);
            }
        }
        return $response;
    }
    private function send_msg($mes, $user_id){
        $request_params = array(
            'message' => $mes, 
            'user_id' => $user_id, 
            'access_token' => config('mod_vk.access_token'), 
            'v' => '5.0' 
        ); 
        $get_params = http_build_query($request_params); 
        $response = json_decode(GameController::curl('https://api.vk.com/method/messages.send?'. $get_params));
        return $response->response; 
    }
    private function check_cmd($user_id, $body, $mes_id){
        if (Cache::has('vk.user.name.' . $user_id . '.form')){
            if($body == '/send'){
                self::sendForm($user_id);
            } else {
                self::Form($user_id, $mes_id);
            }
            return;
        } else {
            $data = explode(" ", $body);
            switch ($data[0]){
                case '/logout':
                    Cache::forget('vk.user.' . $user_id);
                    self::send_msg('Вы вышли из аккаунта', $user_id);
                    return;
                case '/list':
                    self::send_msg('/help<br>/list<br>/form<br>/send<br>/a<br>/logout', $user_id);
                    return;
                case '/a':
                    if(in_array($this->user->steamid64, config('mod_vk.admins'))){
                        if(!isset($data[1])||!isset($data[2])){
                            self::send_msg('/a USER_ID TEXT', $user_id);
                        } else {
                            self::send_msg($data[2], $data[1]);
                            usleep(1000000);
                            self::send_msg('Сообщение отправлено.', $user_id);
                        }
                    } else {
                        self::send_msg('Упс. Вы не админ.', $user_id);
                    }
                    return;
                case '/form':
                    self::send_msg('Вы открыли форму запроса в поддержку. Максимально полно опишите свою проблему, прикрепите скрины и все необходимые материалы в одно сообщение.<br>После пропишите /send для отправки формы.', $user_id);
                    self::Form($user_id, $mes_id);
                    return;
                case '/help':
                    if(isset($data[1])){
                        switch ($data[1]){
                            case 'list':
                                self::send_msg('Показывает список команд', $user_id);
                                return;
                            case 'logout':
                                self::send_msg('Выход из аккаунта', $user_id);
                                return;
                            case 'a':
                                self::send_msg('Ответить на запрос пользователя [админ]', $user_id);
                                return;
                            case 'form':
                                self::send_msg('Формирует обращение в поддерджку', $user_id);
                                return;
                            case 'send':
                                self::send_msg('Отправляет обращение в поддерджку', $user_id);
                                return;
                            default:break;
                        }
                    }
                    self::send_msg('Для подробной справки о команде пропишите<br>/help КОМАНДА<br>/list - список команд', $user_id);
                    return;
                default:
                    self::send_msg('/help - для справки', $user_id);
                    return false;
            }
        }
    }
    private function Form($user_id, $mes_id){
        if (Cache::has('vk.user.name.' . $user_id . '.form')){
            $ids = Cache::get('vk.user.name.' . $user_id . '.form');
            Cache::forever('vk.user.name.' . $user_id . '.form', $ids.','.$mes_id);
        } else {
            $info = self::get_info($user_id);
            Cache::forever('vk.user.name.' . $user_id . '.form', $info['info']['init']);
        }
    }
    private function sendForm($user_id){
        $forward_messages = Cache::get('vk.user.name.' . $user_id . '.form');
        Cache::forget('vk.user.name.' . $user_id . '.form');
        foreach(config('mod_vk.admins') as $admin){
            $request_params = array(
                'message' => 'https://vk.com/gim' . config('mod_vk.group_id') . '?sel=' . $user_id, 
                'user_id' => $admin,
                'forward_messages' => $forward_messages,
                'access_token' => config('mod_vk.access_token'), 
                'v' => '5.0' 
            ); 
            $get_params = http_build_query($request_params); 
            GameController::curl('https://api.vk.com/method/messages.send?'. $get_params);
        }
        return;
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
