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
        if (Cache::has('vk.temp.' . $user_id)){return;} else {Cache::put('vk.temp.' . $user_id, '', 1);}
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
                self::send_msg('Укажите пароль ↑!', $user_id);
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
                self::send_msg('Для авторизации пропишите команду:<br>/login РЕФЕРАЛ ПАРОЛЬ<br>Пароль можно установить в вашем профиле!<br>Максимум 1 сообщение в 2 сек.<br>https://youtu.be/0eMQO7drbsw', $user_id);
            }
        }
        return $response;
    }
    private function send_msg($mes, $user_id){
        $request_params = array(
            'message' => $mes, 
            'user_id' => $user_id, 
            'access_token' => config('mod_vk.access_token'), 
            'v' => '5.62' 
        ); 
        $get_params = http_build_query($request_params); 
        $response = json_decode(GameController::curl('https://api.vk.com/method/messages.send?'. $get_params));
        if(!isset($response->response)) $response = (object)['response'=>''];
        return $response->response; 
    }
    private function check_cmd($user_id, $body, $mes_id){
        $data = explode(" ", $body);
        if (Cache::has('vk.user.name.' . $user_id . '.form')){
            if($data[0] == '/send'){
                if(in_array($user_id, config('mod_vk.admins'))){
                    if(isset($data[1])){
                        self::sendForm($user_id, $data[1]);
                    } else {
                        self::sendForm($user_id, false);
                    }
                } else {
                    self::sendForm($user_id, false);
                }
            } else {
                self::Form($user_id, $mes_id);
            }
            return;
        } else {
            switch ($data[0]){
                case '/login':
                    self::send_msg('Вы уже авторизованы', $user_id);
                    return;
                case '/logout':
                    Cache::forget('vk.user.name.' . $user_id);
                    Cache::forget('vk.user.' . $user_id);
                    self::send_msg('Вы вышли из аккаунта', $user_id);
                    return;
                case '/form':
                    self::send_msg('Максимально полно опишите вашу проблему<br>После пропишите "/send" для отправки формы.', $user_id);
                    self::Form($user_id, $mes_id);
                    return;
                case '/help':
                    if(isset($data[1])){
                        switch ($data[1]){
                            case 'logout':
                                self::send_msg('Выход из аккаунта', $user_id);
                                return;
                            case 'form':
                                self::send_msg('Формирует обращение в поддержку', $user_id);
                                return;
                            case 'send':
                                self::send_msg('Отправляет обращение в поддержку', $user_id);
                                return;
                            default:break;
                        }
                    }
                    self::send_msg('/help КОМАНДА - справка по каждой команде<br>/form - открыть форму запроса<br>/send - закрыть и оптарвить форму<br>/logout - выйти из аккаунта<br>Пример использования help:<br>/help form<br>https://youtu.be/0eMQO7drbsw', $user_id);
                    return;
                default:
                    $datat = array_map('strtolower', $data);
                    if(in_array('как',$datat)&&in_array('дела',$data)){
                        self::send_msg('Нормально, если че - /help - для справки<br>https://youtu.be/0eMQO7drbsw', $user_id);
                    } else if(in_array('привет',$data)) {
                        self::send_msg('Здарова. /help - для справки<br>https://youtu.be/0eMQO7drbsw', $user_id);
                    } else {
                        self::send_msg('/help - для справки<br>https://youtu.be/0eMQO7drbsw', $user_id);
                    }
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
    private function sendForm($user_id, $to_id){
        $forward_messages = Cache::get('vk.user.name.' . $user_id . '.form');
        Cache::forget('vk.user.name.' . $user_id . '.form');
        if(!$to_id){
            $admins = false;
            foreach(config('mod_vk.admins') as $admin) if(!$admins) $admins = $admin; else $admins .= ',' . $admin;
            $request_params = array(
                'message' => 'https://vk.com/gim' . config('mod_vk.group_id') . '?sel=' . $user_id, 
                'user_ids' => $admins,
                'forward_messages' => $forward_messages,
                'access_token' => config('mod_vk.access_token'), 
                'v' => '5.62'
            );
            $get_params = http_build_query($request_params); 
            GameController::curl('https://api.vk.com/method/messages.send?'. $get_params);
        } else {
            $fms = explode(",", $forward_messages); 
            unset($fms[0]);$fma = '';$i = 0;
            foreach($fms as $fm){
                if($i == 0){
                    $fma = $fm;
                } else {
                    $fma .= ','.$fm;
                }
                $i++;
            }
            $request_params = array(
                'message_ids' => $fma, 
                'preview_length' => 0,
                'access_token' => config('mod_vk.access_token'),
                'v' => '5.62' 
            );
            $get_params = http_build_query($request_params); 
            $msges = json_decode(GameController::curl('https://api.vk.com/method/messages.getById?'. $get_params),true);
            $text = '';
            foreach($msges['response']['items'] as $item){
                if(isset($item['body']))$text .= ' ' . $item['body'];
            }
            $request_params = array(
                'message' => $text, 
                'user_ids' => $to_id,
                'access_token' => config('mod_vk.access_token'), 
                'v' => '5.62' 
            );
            $get_params = http_build_query($request_params); 
            GameController::curl('https://api.vk.com/method/messages.send?'. $get_params);
        }
        self::send_msg('Сообщение отправлено.', $user_id);
        return;
    }
    private function getDialogs($i,$j){
        $request_params = array(
            'count' => $i,
            'offset' => 100*$j,
            'preview_length' => 1,
            'unread' => 0,
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
        $users = [];
        for ($i = 0; $i<ceil($count/100);$i++){            
            $dialogs = self::getDialogs(100,$i);
            $dialogs = json_decode($dialogs, true);
            $dialogs = $dialogs['response']['items'];
            foreach($dialogs as $dialog){
                if(isset($dialog['message']['user_id'])){
                    if(!in_array($dialog['message']['user_id'], $users)){
                        $users[] = $dialog['message']['user_id'];
                    }
                }
            }
            usleep(100000);
        }
        foreach($users as $user){
            $this->redis->rpush('vk.to_send.list', json_encode(['id' => $user, 'mes' => $text]));
        }
        return response()->json(['success' => true]);
    }
    public function checkSending(){
        $data = $this->redis->lrange('vk.to_send.list', 0, -1); $i = 0;
        foreach ($data as $json) {
            $this->redis->lrem('vk.to_send.list', 0, $json);
            $info = json_decode($json, true);
            self::send_msg($info['mes'], $info['id']);
            $i++; if($i>3) break;
            usleep(1000000);
        }
        return response()->json(['success' => true]);
    }
}
