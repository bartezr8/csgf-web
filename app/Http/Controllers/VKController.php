<?php

namespace App\Http\Controllers;
use Cache;
use App\User;
use App\Game;
use App\Bot_bet;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
class VKController extends Controller {
	public function index(Request $request){
        if($request->get('group_id') == config('mod_vk.group_id') && $request->get('secret') == config('mod_vk.secret')){
            switch ($request->get('type')){
                case 'confirmation':
                    return config('mod_vk.confirmation_key');
                    break; 
                case 'message_new':
                    self::message_new($request->get('object')['user_id'],$request->get('object')['body']);
                    break;
            }
        }
        return 'ok';
	}
    private function message_new($user_id, $body){
        if (Cache::has('vk.temp.' . $user_id)){return;} else {
            Cache::put('vk.temp.' . $user_id, '', 5);
        }
        if (self::check_cmd($user_id, $body)) return;
        if (Cache::has('vk.stop.' . $user_id)) return;
        if (Cache::has('vk.user.' . $user_id)){
            $user_name = Cache::get('vk.user.' . $user_id);
        } else {
            $user_name = self::get_user($user_id);
            Cache::put('vk.user.' . $user_id, $user_name, 86400);
        }
        $mes = self::parse_text($user_name, $body);
        if($mes) self::send_msg($mes, $user_id);
    }
    private function get_user($user_id){
        $user_info = json_decode(GameController::curl("https://api.vk.com/method/users.get?user_ids=".$user_id."&v=5.0")); 
        $user_name = $user_info->response[0]->first_name;
        return $user_name;
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
    private function parse_text($user_name, $body){
        $body = strtolower($body);
        $words = explode(" ", $body);
        $tc = 0; $max = -1; $response = '';
        foreach (config('mod_vk.types') as $type => $msg){
            $tc = self::count_w($type, $words);
            if($tc > $max){
                $max = $tc;
                $response = $msg;
            }
        }
        return $response;
    }
    private function count_w($type,$arr){
        $counter = 0;
        foreach (config('mod_vk.words')[$type] as $word){
            if(in_array($word, $arr)) $counter++;
        }
        return $counter;//count(config('mod_vk.words')[$type]);
    }
    private function check_cmd($user_id, $body){
        switch ($body){
            case '/start':
                Cache::pull('vk.stop.' . $user_id);
                self::send_msg('Бот на связи!', $user_id);
                return true;
            case '/stop':
                Cache::put('vk.stop.' . $user_id, '', 3600);
                return true;
            case '/help':
                self::send_msg('Список доступных комманд:<br>"/game ***" - показывает статус отправки выигрыша по ботам<br>"/rules" - правила поддержки.<br>"/joke" - получить бесплатную шутку.<br>"/start", "/stop" - включить/выключить автоответчик.', $user_id);
                return true;
            case '/rules':
                self::send_msg('ПРАВИЛА ОБРАЩЕНИЯ В ПОДДЕРЖКУ!<br>Обязательные правила для наиболее быстрого решения проблем:<br>- начинайте свое обращение с темы, если не пришел обмен так и пишите!<br>- указывайте максимально возможное колличество информации и пишите в одно сообщение;<br>- пишите на русскои или английском языке, мы не рассматриваем обращения на других;<br>- не пытайтесь оскорбить или обвинить в мошенничестве администраторов сервиса;<br>- не пытайтесь угрожать или манипулировать администраторами сообщества;<br>- не пытайтесь добиться подкрутки или информации о победителе;<br>- если у вас проблемы с получением вещей, но вы не указали номер игры - бан в поддежке<br>', $user_id);
                return true;
            case '/joke':
                self::send_msg(config('mod_vk.jokes')[rand(0,count(config('mod_vk.jokes'))-1)], $user_id);
                return true;
            default:
                if(preg_match('/game \d{1,6}/', $body, $matches, PREG_OFFSET_CAPTURE)){
                    $id = str_replace("game ", "", $matches[0][0]);
                    self::parse_game($id, $user_id);
                    return true;
                }
                return false;
        }
    }
    private function parse_game($game_id,$user_id){
        $game = Game::find($game_id);
        if(is_null($game)){
            self::send_msg('Игра не найдена!', $user_id);
            return;
        } else {
            $status = 'Неизвестен';
            switch($game->status_prize){
                case 0: $status = 'Отправляется'; break;
                case 1: $status = 'Отпправлен'; break;
                case 2: $status = 'Ошибка отправки'; break;
            }
            $bot_bets = Bot_bet::where('game_id', $game->id)->get();
            $message = 'Информация об игре #'.$game->id.'<br>';
            $message .= 'Статус отправки выигрыша:'. $status.'<br>';
            if($game->status_prize == 2){
                foreach($bot_bets as $bet){
                    $status = 'Неизвестен';
                    switch($bet->status){
                        case 0: $status = 'Отправляется'; break;
                        case 1: $status = 'Отпправлен'; break;
                        case 2: $status = 'Ошибка отправки'; break;
                    }
                    $message .= 'Bot#'.$bet->botid.' | Статус: '.$status.'<br>';
                }
            }
            self::send_msg($message, $user_id);
            return;
        }
    }
}
