<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Object_;
use App\User;
use LRedis;

class ChatController extends Controller
{

    const CHAT_CHANNEL = 'chat.message';
    const NEW_MSG_CHANNEL = 'new.msg';

    public function __construct(){
        parent::__construct();
    }
    
    public function add_message(Request $request){
        if (\Cache::has('addmsg.user.' . $this->user->id)) {
            return response()->json(['message' => 'Вы слишком часто отправляете сообщения!', 'status' => 'error']);
        }
        \Cache::put('addmsg.user.' . $this->user->id, 'message', 1);
        $bet = \DB::table('bets')->where('user_id', '=', $this->user->id)->get();
        if ($this->user->banchat == 1) {
            return response()->json(['message' => 'Вы забанены в чате ! Срок : Навсегда', 'status' => 'error']);
        }
        $userid = $this->user->steamid64;
        $admin = $this->user->is_admin;
		$moder = $this->user->is_moderator;
		$vip = 0;
		if (strpos(strtolower(' '.$this->user->username),  strtolower(config('app.sitename'))) != false) $vip = 1;
        $username = htmlspecialchars($this->user->username);
        $avatar = $this->user->avatar;
        $time = date('H:i', time());
        $messages = strtolower($this->_validateMessage($request));
		function object_to_array($data){
            if (is_array($data) || is_object($data)) {
                $result = array();
                foreach ($data as $key => $value) {
                    $result[$key] = object_to_array($value);
                }
                return $result;
            }
            return $data;
        }
		$words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
        $words = object_to_array(json_decode($words));
		$messages = mb_strtolower($messages);
        foreach ($words as $key => $value) {
            $messages = str_ireplace($key, $value, $messages);
        }
        if ($this->user->is_admin == 0) {
            if ($bet == null) {
               return response()->json(['message' => 'Вы должны поставить ставку чтобы писать в чате', 'status' => 'error']);
            }
        }
        if ($this->user->is_admin == 1) {
            if (substr_count($messages, '/clear')) {
                $this->redis->del(self::CHAT_CHANNEL);
                return response()->json(['message' => 'Вы отчистили чат !', 'status' => 'success']);
            }
        }
        /*if (preg_match("/href|url|http|www|.ru|.com|.net|.info|.org/i", $messages)) {
            return response()->json(['message' => 'Ссылки запрещены !', 'status' => 'error']);
        }*/
		$data = $this->redis->rpop(self::CHAT_CHANNEL);
		if(!is_null($data)){
			$data = json_decode($data, true);
			if($data['userid'] == $userid){
				$data['messages'] = $data['messages'] . ' ' . htmlspecialchars($messages);
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($data));
			} else{
				$returnValue = [
					'userid' => $userid, 
					'avatar' => $avatar, 
					'time' => $time, 
					'messages' => htmlspecialchars($messages), 
					'username' => $username, 
					'admin' => $admin, 
					'vip' => $vip, 
					'moder' => $moder
				];
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($data));
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($returnValue));
			}
		} else {
			$returnValue = [
				'userid' => $userid, 
				'avatar' => $avatar, 
				'time' => $time, 
				'messages' => htmlspecialchars($messages), 
				'username' => $username, 
				'admin' => $admin, 
				'vip' => $vip, 
				'moder' => $moder
			];
			$this->redis->rpush(self::CHAT_CHANNEL, json_encode($returnValue));
		}
        $this->redis->publish(self::NEW_MSG_CHANNEL, json_encode(['status' => 'success']));
        return response()->json(['message' => 'Ваше сообщение успешно отправлено !', 'status' => 'success']);
    }


    private function _validateMessage($request){
        $val = \Validator::make($request->all(), [
            'messages' => 'required|string|max:255'
        ], [
            'required' => 'Сообщение не может быть пустым!',
            'string' => 'Сообщение должно быть строкой!',
            'max' => 'Максимальный размер сообщения 255 символов.',
        ]);
        if ($val->fails()) $this->throwValidationException($request, $val);
        return $request->get('messages');
    }
    private function replaceSmile($a){
        $a = str_replace(":b:", "<b>", $a);
        $a = str_replace(":/b:", "</b>", $a);
        $a = str_replace(":br:", "<br>", $a);
        $a = str_replace(":писюн:", "<img style=\"background:none;wigth:100px;height:100px\"id=smile src=\"http://www.tongabonga.com/media/images/4/uncut-latino-gay/uncut-latino-gay-73331.jpg\">", $a);
        $a = str_replace("skoniks", " :sm104:<a href='http://natribu.org/'><b>SKONIKS</b></a>:sm104: ", $a);
        $a = str_replace(":csgf:", "<img style=\"background:none;\" id=smile src=\"/assets/img/". config('app.logo') ."\">", $a);
        $a = str_replace(":sm1:", "<a id=\"smile\" class=\"smile-smile-_1_\"></a>", $a);
        $a = str_replace(":sm2:", "<a id=\"smile\" class=\"smile-smile-_2_\"></a>", $a);
        $a = str_replace(":sm3:", "<a id=\"smile\" class=\"smile-smile-_3_\"></a>", $a);
        $a = str_replace(":sm4:", "<a id=\"smile\" class=\"smile-smile-_4_\"></a>", $a);
        $a = str_replace(":sm5:", "<a id=\"smile\" class=\"smile-smile-_5_\"></a>", $a);
        $a = str_replace(":sm6:", "<a id=\"smile\" class=\"smile-smile-_6_\"></a>", $a);
        $a = str_replace(":sm7:", "<a id=\"smile\" class=\"smile-smile-_7_\"></a>", $a);
        $a = str_replace(":sm8:", "<a id=\"smile\" class=\"smile-smile-_8_\"></a>", $a);
        $a = str_replace(":sm9:", "<a id=\"smile\" class=\"smile-smile-_9_\"></a>", $a);
        $a = str_replace(":sm10:", "<a id=\"smile\" class=\"smile-smile-_10_\"></a>", $a);
        $a = str_replace(":sm11:", "<a id=\"smile\" class=\"smile-smile-_11_\"></a>", $a);
        $a = str_replace(":sm12:", "<a id=\"smile\" class=\"smile-smile-_12_\"></a>", $a);
        $a = str_replace(":sm13:", "<a id=\"smile\" class=\"smile-smile-_13_\"></a>", $a);
        $a = str_replace(":sm14:", "<a id=\"smile\" class=\"smile-smile-_14_\"></a>", $a);
        $a = str_replace(":sm15:", "<a id=\"smile\" class=\"smile-smile-_15_\"></a>", $a);
        $a = str_replace(":sm16:", "<a id=\"smile\" class=\"smile-smile-_16_\"></a>", $a);
        $a = str_replace(":sm17:", "<a id=\"smile\" class=\"smile-smile-_17_\"></a>", $a);
        $a = str_replace(":sm18:", "<a id=\"smile\" class=\"smile-smile-_18_\"></a>", $a);
        $a = str_replace(":sm19:", "<a id=\"smile\" class=\"smile-smile-_19_\"></a>", $a);
        $a = str_replace(":sm20:", "<a id=\"smile\" class=\"smile-smile-_20_\"></a>", $a);
        $a = str_replace(":sm21:", "<a id=\"smile\" class=\"smile-smile-_21_\"></a>", $a);
        $a = str_replace(":sm22:", "<a id=\"smile\" class=\"smile-smile-_22_\"></a>", $a);
        $a = str_replace(":sm23:", "<a id=\"smile\" class=\"smile-smile-_23_\"></a>", $a);
        $a = str_replace(":sm24:", "<a id=\"smile\" class=\"smile-smile-_24_\"></a>", $a);
        $a = str_replace(":sm25:", "<a id=\"smile\" class=\"smile-smile-_25_\"></a>", $a);
        $a = str_replace(":sm26:", "<a id=\"smile\" class=\"smile-smile-_26_\"></a>", $a);
        $a = str_replace(":sm27:", "<a id=\"smile\" class=\"smile-smile-_27_\"></a>", $a);
        $a = str_replace(":sm28:", "<a id=\"smile\" class=\"smile-smile-_28_\"></a>", $a);
        $a = str_replace(":sm29:", "<a id=\"smile\" class=\"smile-smile-_29_\"></a>", $a);
        $a = str_replace(":sm30:", "<a id=\"smile\" class=\"smile-smile-_30_\"></a>", $a);
        $a = str_replace(":sm31:", "<a id=\"smile\" class=\"smile-smile-_31_\"></a>", $a);
        $a = str_replace(":sm32:", "<a id=\"smile\" class=\"smile-smile-_32_\"></a>", $a);
        $a = str_replace(":sm33:", "<a id=\"smile\" class=\"smile-smile-_33_\"></a>", $a);
        $a = str_replace(":sm34:", "<a id=\"smile\" class=\"smile-smile-_34_\"></a>", $a);
        $a = str_replace(":sm35:", "<a id=\"smile\" class=\"smile-smile-_35_\"></a>", $a);
        $a = str_replace(":sm36:", "<a id=\"smile\" class=\"smile-smile-_36_\"></a>", $a);
        $a = str_replace(":sm37:", "<a id=\"smile\" class=\"smile-smile-_37_\"></a>", $a);
        $a = str_replace(":sm38:", "<a id=\"smile\" class=\"smile-smile-_38_\"></a>", $a);
        $a = str_replace(":sm39:", "<a id=\"smile\" class=\"smile-smile-_39_\"></a>", $a);
        $a = str_replace(":sm40:", "<a id=\"smile\" class=\"smile-smile-_40_\"></a>", $a);
        $a = str_replace(":sm41:", "<a id=\"smile\" class=\"smile-smile-_41_\"></a>", $a);
        $a = str_replace(":sm42:", "<a id=\"smile\" class=\"smile-smile-_42_\"></a>", $a);
        $a = str_replace(":sm43:", "<a id=\"smile\" class=\"smile-smile-_43_\"></a>", $a);
        $a = str_replace(":sm44:", "<a id=\"smile\" class=\"smile-smile-_44_\"></a>", $a);
        $a = str_replace(":sm45:", "<a id=\"smile\" class=\"smile-smile-_45_\"></a>", $a);
        $a = str_replace(":sm46:", "<a id=\"smile\" class=\"smile-smile-_46_\"></a>", $a);
        $a = str_replace(":sm47:", "<a id=\"smile\" class=\"smile-smile-_47_\"></a>", $a);
        $a = str_replace(":sm48:", "<a id=\"smile\" class=\"smile-smile-_48_\"></a>", $a);
        $a = str_replace(":sm49:", "<a id=\"smile\" class=\"smile-smile-_49_\"></a>", $a);
        $a = str_replace(":sm50:", "<a id=\"smile\" class=\"smile-smile-_50_\"></a>", $a);
        $a = str_replace(":sm51:", "<a id=\"smile\" class=\"smile-smile-_51_\"></a>", $a);
        $a = str_replace(":sm52:", "<a id=\"smile\" class=\"smile-smile-_52_\"></a>", $a);
        $a = str_replace(":sm53:", "<a id=\"smile\" class=\"smile-smile-_53_\"></a>", $a);
        $a = str_replace(":sm54:", "<a id=\"smile\" class=\"smile-smile-_54_\"></a>", $a);
        $a = str_replace(":sm55:", "<a id=\"smile\" class=\"smile-smile-_55_\"></a>", $a);
        $a = str_replace(":sm56:", "<a id=\"smile\" class=\"smile-smile-_56_\"></a>", $a);
        $a = str_replace(":sm57:", "<a id=\"smile\" class=\"smile-smile-_57_\"></a>", $a);
        $a = str_replace(":sm58:", "<a id=\"smile\" class=\"smile-smile-_58_\"></a>", $a);
        $a = str_replace(":sm59:", "<a id=\"smile\" class=\"smile-smile-_59_\"></a>", $a);
        $a = str_replace(":sm60:", "<a id=\"smile\" class=\"smile-smile-_60_\"></a>", $a);
        $a = str_replace(":sm61:", "<a id=\"smile\" class=\"smile-smile-_61_\"></a>", $a);
        $a = str_replace(":sm62:", "<a id=\"smile\" class=\"smile-smile-_62_\"></a>", $a);
        $a = str_replace(":sm63:", "<a id=\"smile\" class=\"smile-smile-_63_\"></a>", $a);
        $a = str_replace(":sm64:", "<a id=\"smile\" class=\"smile-smile-_64_\"></a>", $a);
        $a = str_replace(":sm65:", "<a id=\"smile\" class=\"smile-smile-_65_\"></a>", $a);
        $a = str_replace(":sm66:", "<a id=\"smile\" class=\"smile-smile-_66_\"></a>", $a);
        $a = str_replace(":sm67:", "<a id=\"smile\" class=\"smile-smile-_67_\"></a>", $a);
        $a = str_replace(":sm68:", "<a id=\"smile\" class=\"smile-smile-_68_\"></a>", $a);
        $a = str_replace(":sm69:", "<a id=\"smile\" class=\"smile-smile-_69_\"></a>", $a);
        $a = str_replace(":sm70:", "<a id=\"smile\" class=\"smile-smile-_70_\"></a>", $a);
        $a = str_replace(":sm71:", "<a id=\"smile\" class=\"smile-smile-_71_\"></a>", $a);
        $a = str_replace(":sm72:", "<a id=\"smile\" class=\"smile-smile-_72_\"></a>", $a);
        $a = str_replace(":sm73:", "<a id=\"smile\" class=\"smile-smile-_73_\"></a>", $a);
        $a = str_replace(":sm74:", "<a id=\"smile\" class=\"smile-smile-_74_\"></a>", $a);
        $a = str_replace(":sm75:", "<a id=\"smile\" class=\"smile-smile-_75_\"></a>", $a);
        $a = str_replace(":sm76:", "<a id=\"smile\" class=\"smile-smile-_76_\"></a>", $a);
        $a = str_replace(":sm77:", "<a id=\"smile\" class=\"smile-smile-_77_\"></a>", $a);
        $a = str_replace(":sm78:", "<a id=\"smile\" class=\"smile-smile-_78_\"></a>", $a);
        $a = str_replace(":sm79:", "<a id=\"smile\" class=\"smile-smile-_79_\"></a>", $a);
        $a = str_replace(":sm80:", "<a id=\"smile\" class=\"smile-smile-_80_\"></a>", $a);
        $a = str_replace(":sm81:", "<a id=\"smile\" class=\"smile-smile-_81_\"></a>", $a);
        $a = str_replace(":sm82:", "<a id=\"smile\" class=\"smile-smile-_82_\"></a>", $a);
        $a = str_replace(":sm83:", "<a id=\"smile\" class=\"smile-smile-_83_\"></a>", $a);
        $a = str_replace(":sm84:", "<a id=\"smile\" class=\"smile-smile-_84_\"></a>", $a);
        $a = str_replace(":sm85:", "<a id=\"smile\" class=\"smile-smile-_85_\"></a>", $a);
        $a = str_replace(":sm86:", "<a id=\"smile\" class=\"smile-smile-_86_\"></a>", $a);
        $a = str_replace(":sm87:", "<a id=\"smile\" class=\"smile-smile-_87_\"></a>", $a);
        $a = str_replace(":sm88:", "<a id=\"smile\" class=\"smile-smile-_88_\"></a>", $a);
        $a = str_replace(":sm89:", "<a id=\"smile\" class=\"smile-smile-_89_\"></a>", $a);
        $a = str_replace(":sm90:", "<a id=\"smile\" class=\"smile-smile-_90_\"></a>", $a);
        $a = str_replace(":sm91:", "<a id=\"smile\" class=\"smile-smile-_91_\"></a>", $a);
        $a = str_replace(":sm92:", "<a id=\"smile\" class=\"smile-smile-_92_\"></a>", $a);
        $a = str_replace(":sm93:", "<a id=\"smile\" class=\"smile-smile-_93_\"></a>", $a);
        $a = str_replace(":sm94:", "<a id=\"smile\" class=\"smile-smile-_94_\"></a>", $a);
        $a = str_replace(":sm95:", "<a id=\"smile\" class=\"smile-smile-_95_\"></a>", $a);
        $a = str_replace(":sm96:", "<a id=\"smile\" class=\"smile-smile-_96_\"></a>", $a);
        $a = str_replace(":sm97:", "<a id=\"smile\" class=\"smile-smile-_97_\"></a>", $a);
        $a = str_replace(":sm98:", "<a id=\"smile\" class=\"smile-smile-_98_\"></a>", $a);
        $a = str_replace(":sm99:", "<a id=\"smile\" class=\"smile-smile-_99_\"></a>", $a);
        $a = str_replace(":sm100:", "<a id=\"smile\" class=\"smile-smile-_100_\"></a>", $a);
        $a = str_replace(":sm101:", "<a id=\"smile\" class=\"smile-smile-_101_\"></a>", $a);
        $a = str_replace(":sm102:", "<a id=\"smile\" class=\"smile-smile-_102_\"></a>", $a);
        $a = str_replace(":sm103:", "<a id=\"smile\" class=\"smile-smile-_103_\"></a>", $a);
        $a = str_replace(":sm104:", "<a id=\"smile\" class=\"smile-smile-_104_\"></a>", $a);
        $a = str_replace(":sm105:", "<a id=\"smile\" class=\"smile-smile-_105_\"></a>", $a);
        $a = str_replace(":sm106:", "<a id=\"smile\" class=\"smile-smile-_106_\"></a>", $a);
        $a = str_replace(":sm107:", "<a id=\"smile\" class=\"smile-smile-_107_\"></a>", $a);
        $a = str_replace(":sm108:", "<a id=\"smile\" class=\"smile-smile-_108_\"></a>", $a);
        $a = str_replace(":sm109:", "<a id=\"smile\" class=\"smile-smile-_109_\"></a>", $a);
        $a = str_replace(":sm110:", "<a id=\"smile\" class=\"smile-smile-_110_\"></a>", $a);
        $a = str_replace(":sm111:", "<a id=\"smile\" class=\"smile-smile-_111_\"></a>", $a);
        $a = str_replace(":sm112:", "<a id=\"smile\" class=\"smile-smile-_112_\"></a>", $a);
        $a = str_replace(":sm113:", "<a id=\"smile\" class=\"smile-smile-_113_\"></a>", $a);
        $a = str_replace(":sm114:", "<a id=\"smile\" class=\"smile-smile-_114_\"></a>", $a);
        $a = str_replace(":sm115:", "<a id=\"smile\" class=\"smile-smile-_115_\"></a>", $a);
        $a = str_replace(":sm116:", "<a id=\"smile\" class=\"smile-smile-_116_\"></a>", $a);
        $a = str_replace(":sm117:", "<a id=\"smile\" class=\"smile-smile-_117_\"></a>", $a);
        $a = str_replace(":sm118:", "<a id=\"smile\" class=\"smile-smile-_118_\"></a>", $a);
        $a = str_replace(":sm119:", "<a id=\"smile\" class=\"smile-smile-_119_\"></a>", $a);
        $a = str_replace(":sm120:", "<a id=\"smile\" class=\"smile-smile-_120_\"></a>", $a);
        $a = str_replace(":sm121:", "<a id=\"smile\" class=\"smile-smile-_121_\"></a>", $a);
        $a = str_replace(":sm122:", "<a id=\"smile\" class=\"smile-smile-_122_\"></a>", $a);
        $a = str_replace(":sm123:", "<a id=\"smile\" class=\"smile-smile-_123_\"></a>", $a);
        $a = str_replace(":sm124:", "<a id=\"smile\" class=\"smile-smile-_124_\"></a>", $a);
        $a = str_replace(":sm125:", "<a id=\"smile\" class=\"smile-smile-_125_\"></a>", $a);
        $a = str_replace(":sm126:", "<a id=\"smile\" class=\"smile-smile-_126_\"></a>", $a);
        $a = str_replace(":sm127:", "<a id=\"smile\" class=\"smile-smile-_127_\"></a>", $a);
        $a = str_replace(":sm128:", "<a id=\"smile\" class=\"smile-smile-_128_\"></a>", $a);
        $a = str_replace(":sm129:", "<a id=\"smile\" class=\"smile-smile-_129_\"></a>", $a);
        $a = str_replace(":sm130:", "<a id=\"smile\" class=\"smile-smile-_130_\"></a>", $a);
        $a = str_replace(":sm131:", "<a id=\"smile\" class=\"smile-smile-_131_\"></a>", $a);
        $a = str_replace(":sm132:", "<a id=\"smile\" class=\"smile-smile-_132_\"></a>", $a);
        $a = str_replace(":sm133:", "<a id=\"smile\" class=\"smile-smile-_133_\"></a>", $a);
        $a = str_replace(":sm134:", "<a id=\"smile\" class=\"smile-smile-_134_\"></a>", $a);
        $a = str_replace(":sm135:", "<a id=\"smile\" class=\"smile-smile-_135_\"></a>", $a);
        $a = str_replace(":sm136:", "<a id=\"smile\" class=\"smile-smile-_136_\"></a>", $a);
        $a = str_replace(":sm137:", "<a id=\"smile\" class=\"smile-smile-_137_\"></a>", $a);
        $a = str_replace(":sm138:", "<a id=\"smile\" class=\"smile-smile-_138_\"></a>", $a);
        $a = str_replace(":sm139:", "<a id=\"smile\" class=\"smile-smile-_139_\"></a>", $a);
        $a = str_replace(":sm140:", "<a id=\"smile\" class=\"smile-smile-_140_\"></a>", $a);
        $a = str_replace(":sm141:", "<a id=\"smile\" class=\"smile-smile-_141_\"></a>", $a);
        $a = str_replace(":sm142:", "<a id=\"smile\" class=\"smile-smile-_142_\"></a>", $a);
        $a = str_replace(":sm143:", "<a id=\"smile\" class=\"smile-smile-_143_\"></a>", $a);
        $a = str_replace(":sm144:", "<a id=\"smile\" class=\"smile-smile-_144_\"></a>", $a);
        $a = str_replace(":sm145:", "<a id=\"smile\" class=\"smile-smile-_145_\"></a>", $a);
        $a = str_replace(":sm146:", "<a id=\"smile\" class=\"smile-smile-_146_\"></a>", $a);
        $a = str_replace(":sm147:", "<a id=\"smile\" class=\"smile-smile-_147_\"></a>", $a);
        $a = str_replace(":sm148:", "<a id=\"smile\" class=\"smile-smile-_148_\"></a>", $a);
        $a = str_replace(":sm149:", "<a id=\"smile\" class=\"smile-smile-_149_\"></a>", $a);
        $a = str_replace(":sm150:", "<a id=\"smile\" class=\"smile-smile-_150_\"></a>", $a);
        $a = str_replace(":sm151:", "<a id=\"smile\" class=\"smile-smile-_151_\"></a>", $a);
        $a = str_replace(":sm152:", "<a id=\"smile\" class=\"smile-smile-_152_\"></a>", $a);
        $a = str_replace(":sm153:", "<a id=\"smile\" class=\"smile-smile-_153_\"></a>", $a);
        $a = str_replace(":sm154:", "<a id=\"smile\" class=\"smile-smile-_154_\"></a>", $a);
        $a = str_replace(":sm155:", "<a id=\"smile\" class=\"smile-smile-_155_\"></a>", $a);
        $a = str_replace(":sm156:", "<a id=\"smile\" class=\"smile-smile-_156_\"></a>", $a);
        $a = str_replace(":sm157:", "<a id=\"smile\" class=\"smile-smile-_157_\"></a>", $a);
        $a = str_replace(":sm158:", "<a id=\"smile\" class=\"smile-smile-_158_\"></a>", $a);
        $a = str_replace(":sm159:", "<a id=\"smile\" class=\"smile-smile-_159_\"></a>", $a);
        $a = str_replace(":sm160:", "<a id=\"smile\" class=\"smile-smile-_160_\"></a>", $a);
        $a = str_replace(":sm161:", "<a id=\"smile\" class=\"smile-smile-_161_\"></a>", $a);
        $a = str_replace(":sm162:", "<a id=\"smile\" class=\"smile-smile-_162_\"></a>", $a);
        $a = str_replace(":sm163:", "<a id=\"smile\" class=\"smile-smile-_163_\"></a>", $a);
        $a = str_replace(":sm164:", "<a id=\"smile\" class=\"smile-smile-_164_\"></a>", $a);
        $a = str_replace(":sm165:", "<a id=\"smile\" class=\"smile-smile-_165_\"></a>", $a);
        $a = str_replace(":sm166:", "<a id=\"smile\" class=\"smile-smile-_166_\"></a>", $a);
        $a = str_replace(":sm167:", "<a id=\"smile\" class=\"smile-smile-_167_\"></a>", $a);
        $a = str_replace(":sm168:", "<a id=\"smile\" class=\"smile-smile-_168_\"></a>", $a);
        $a = str_replace(":sm169:", "<a id=\"smile\" class=\"smile-smile-_169_\"></a>", $a);
        $a = str_replace(":sm170:", "<a id=\"smile\" class=\"smile-smile-_170_\"></a>", $a);
        $a = str_replace(":sm171:", "<a id=\"smile\" class=\"smile-smile-_171_\"></a>", $a);
        $a = str_replace(":sm172:", "<a id=\"smile\" class=\"smile-smile-_172_\"></a>", $a);
        $a = str_replace(":sm173:", "<a id=\"smile\" class=\"smile-smile-_173_\"></a>", $a);
        $a = str_replace(":sm174:", "<a id=\"smile\" class=\"smile-smile-_174_\"></a>", $a);
        $a = str_replace(":sm175:", "<a id=\"smile\" class=\"smile-smile-_175_\"></a>", $a);
        $a = str_replace(":sm176:", "<a id=\"smile\" class=\"smile-smile-_176_\"></a>", $a);
        $a = str_replace(":sm177:", "<a id=\"smile\" class=\"smile-smile-_177_\"></a>", $a);
        $a = str_replace(":sm178:", "<a id=\"smile\" class=\"smile-smile-_178_\"></a>", $a);
        $a = str_replace(":sm179:", "<a id=\"smile\" class=\"smile-smile-_179_\"></a>", $a);
        $a = str_replace(":sm180:", "<a id=\"smile\" class=\"smile-smile-_180_\"></a>", $a);
        $a = str_replace(":sm181:", "<a id=\"smile\" class=\"smile-smile-_181_\"></a>", $a);
        $a = str_replace(":sm182:", "<a id=\"smile\" class=\"smile-smile-_182_\"></a>", $a);
        $a = str_replace(":sm183:", "<a id=\"smile\" class=\"smile-smile-_183_\"></a>", $a);
        $a = str_replace(":sm184:", "<a id=\"smile\" class=\"smile-smile-_184_\"></a>", $a);
        $a = str_replace(":sm185:", "<a id=\"smile\" class=\"smile-smile-_185_\"></a>", $a);
        $a = str_replace(":sm186:", "<a id=\"smile\" class=\"smile-smile-_186_\"></a>", $a);
        $a = str_replace(":sm187:", "<a id=\"smile\" class=\"smile-smile-_187_\"></a>", $a);
        $a = str_replace(":sm188:", "<a id=\"smile\" class=\"smile-smile-_188_\"></a>", $a);
        $a = str_replace(":sm189:", "<a id=\"smile\" class=\"smile-smile-_189_\"></a>", $a);
        $a = str_replace(":sm190:", "<a id=\"smile\" class=\"smile-smile-_190_\"></a>", $a);
        $a = str_replace(":sm191:", "<a id=\"smile\" class=\"smile-smile-_191_\"></a>", $a);
        $a = str_replace(":sm192:", "<a id=\"smile\" class=\"smile-smile-_192_\"></a>", $a);
        $a = str_replace(":sm193:", "<a id=\"smile\" class=\"smile-smile-_193_\"></a>", $a);
        $a = str_replace(":sm194:", "<a id=\"smile\" class=\"smile-smile-_194_\"></a>", $a);
        $a = str_replace(":sm195:", "<a id=\"smile\" class=\"smile-smile-_195_\"></a>", $a);
        $a = str_replace(":sm196:", "<a id=\"smile\" class=\"smile-smile-_196_\"></a>", $a);
        $a = str_replace(":sm197:", "<a id=\"smile\" class=\"smile-smile-_197_\"></a>", $a);
        $a = str_replace(":sm198:", "<a id=\"smile\" class=\"smile-smile-_198_\"></a>", $a);
        $a = str_replace(":sm199:", "<a id=\"smile\" class=\"smile-smile-_199_\"></a>", $a);
        $a = str_replace(":sm200:", "<a id=\"smile\" class=\"smile-smile-_200_\"></a>", $a);
        $a = str_replace(":sm201:", "<a id=\"smile\" class=\"smile-smile-_201_\"></a>", $a);
        $a = str_replace(":sm202:", "<a id=\"smile\" class=\"smile-smile-_202_\"></a>", $a);
        $a = str_replace(":sm203:", "<a id=\"smile\" class=\"smile-smile-_203_\"></a>", $a);
        $a = str_replace(":sm204:", "<a id=\"smile\" class=\"smile-smile-_204_\"></a>", $a);
        $a = str_replace(":sm205:", "<a id=\"smile\" class=\"smile-smile-_205_\"></a>", $a);
        $a = str_replace(":sm206:", "<a id=\"smile\" class=\"smile-smile-_206_\"></a>", $a);
        $a = str_replace(":sm207:", "<a id=\"smile\" class=\"smile-smile-_207_\"></a>", $a);
        $a = str_replace(":sm208:", "<a id=\"smile\" class=\"smile-smile-_208_\"></a>", $a);
        $a = str_replace(":sm209:", "<a id=\"smile\" class=\"smile-smile-_209_\"></a>", $a);
        $a = str_replace(":sm210:", "<a id=\"smile\" class=\"smile-smile-_210_\"></a>", $a);
        $a = str_replace(":sm211:", "<a id=\"smile\" class=\"smile-smile-_211_\"></a>", $a);
        $a = str_replace(":sm212:", "<a id=\"smile\" class=\"smile-smile-_212_\"></a>", $a);
        $a = str_replace(":sm213:", "<a id=\"smile\" class=\"smile-smile-_213_\"></a>", $a);
        $a = str_replace(":sm214:", "<a id=\"smile\" class=\"smile-smile-_214_\"></a>", $a);
        $a = str_replace(":sm215:", "<a id=\"smile\" class=\"smile-smile-_215_\"></a>", $a);
        $a = str_replace(":sm216:", "<a id=\"smile\" class=\"smile-smile-_216_\"></a>", $a);
        $a = str_replace(":sm217:", "<a id=\"smile\" class=\"smile-smile-_217_\"></a>", $a);
        $a = str_replace(":sm218:", "<a id=\"smile\" class=\"smile-smile-_218_\"></a>", $a);
        $a = str_replace(":sm219:", "<a id=\"smile\" class=\"smile-smile-_219_\"></a>", $a);
        $a = str_replace(":sm220:", "<a id=\"smile\" class=\"smile-smile-_220_\"></a>", $a);
        $a = str_replace(":sm221:", "<a id=\"smile\" class=\"smile-smile-_221_\"></a>", $a);
        $a = str_replace(":sm222:", "<a id=\"smile\" class=\"smile-smile-_222_\"></a>", $a);
        $a = str_replace(":sm223:", "<a id=\"smile\" class=\"smile-smile-_223_\"></a>", $a);
        $a = str_replace(":sm224:", "<a id=\"smile\" class=\"smile-smile-_224_\"></a>", $a);
        $a = str_replace(":sm225:", "<a id=\"smile\" class=\"smile-smile-_225_\"></a>", $a);
        $a = str_replace(":sm226:", "<a id=\"smile\" class=\"smile-smile-_226_\"></a>", $a);
        $a = str_replace(":sm227:", "<a id=\"smile\" class=\"smile-smile-_227_\"></a>", $a);
        $a = str_replace(":sm228:", "<a id=\"smile\" class=\"smile-smile-_228_\"></a>", $a);
        $a = str_replace(":sm229:", "<a id=\"smile\" class=\"smile-smile-_229_\"></a>", $a);
        $a = str_replace(":sm230:", "<a id=\"smile\" class=\"smile-smile-_230_\"></a>", $a);
        $a = str_replace(":sm231:", "<a id=\"smile\" class=\"smile-smile-_231_\"></a>", $a);
        $a = str_replace(":sm232:", "<a id=\"smile\" class=\"smile-smile-_232_\"></a>", $a);
        $a = str_replace(":sm233:", "<a id=\"smile\" class=\"smile-smile-_233_\"></a>", $a);
        $a = str_replace(":sm234:", "<a id=\"smile\" class=\"smile-smile-_234_\"></a>", $a);
        $a = str_replace(":sm235:", "<a id=\"smile\" class=\"smile-smile-_235_\"></a>", $a);
        $a = str_replace(":sm236:", "<a id=\"smile\" class=\"smile-smile-_236_\"></a>", $a);
        $a = str_replace(":sm237:", "<a id=\"smile\" class=\"smile-smile-_237_\"></a>", $a);
        $a = str_replace(":sm238:", "<a id=\"smile\" class=\"smile-smile-_238_\"></a>", $a);
        $a = str_replace(":sm239:", "<a id=\"smile\" class=\"smile-smile-_239_\"></a>", $a);
        $a = str_replace(":sm240:", "<a id=\"smile\" class=\"smile-smile-_240_\"></a>", $a);
        $a = str_replace(":sm241:", "<a id=\"smile\" class=\"smile-smile-_241_\"></a>", $a);
        $a = str_replace(":sm242:", "<a id=\"smile\" class=\"smile-smile-_242_\"></a>", $a);
        $a = str_replace(":sm243:", "<a id=\"smile\" class=\"smile-smile-_243_\"></a>", $a);
        $a = str_replace(":sm244:", "<a id=\"smile\" class=\"smile-smile-_244_\"></a>", $a);
        $a = str_replace(":sm245:", "<a id=\"smile\" class=\"smile-smile-_245_\"></a>", $a);
        $a = str_replace(":sm246:", "<a id=\"smile\" class=\"smile-smile-_246_\"></a>", $a);
        $a = str_replace(":sm247:", "<a id=\"smile\" class=\"smile-smile-_247_\"></a>", $a);
        $a = str_replace(":sm248:", "<a id=\"smile\" class=\"smile-smile-_248_\"></a>", $a);
        $a = str_replace(":sm249:", "<a id=\"smile\" class=\"smile-smile-_249_\"></a>", $a);
        $a = str_replace(":sm250:", "<a id=\"smile\" class=\"smile-smile-_250_\"></a>", $a);
        $a = str_replace(":sm251:", "<a id=\"smile\" class=\"smile-smile-_251_\"></a>", $a);
        $a = str_replace(":sm252:", "<a id=\"smile\" class=\"smile-smile-_252_\"></a>", $a);
        $a = str_replace(":sm253:", "<a id=\"smile\" class=\"smile-smile-_253_\"></a>", $a);
        $a = str_replace(":sm254:", "<a id=\"smile\" class=\"smile-smile-_254_\"></a>", $a);
        $a = str_replace(":sm255:", "<a id=\"smile\" class=\"smile-smile-_255_\"></a>", $a);
        $a = str_replace(":sm256:", "<a id=\"smile\" class=\"smile-smile-_256_\"></a>", $a);
        $a = str_replace(":sm257:", "<a id=\"smile\" class=\"smile-smile-_257_\"></a>", $a);
        $a = str_replace(":sm258:", "<a id=\"smile\" class=\"smile-smile-_258_\"></a>", $a);
        $a = str_replace(":sm259:", "<a id=\"smile\" class=\"smile-smile-_259_\"></a>", $a);
        $a = str_replace(":sm260:", "<a id=\"smile\" class=\"smile-smile-_260_\"></a>", $a);
        $a = str_replace(":sm261:", "<a id=\"smile\" class=\"smile-smile-_261_\"></a>", $a);
        $a = str_replace(":sm262:", "<a id=\"smile\" class=\"smile-smile-_262_\"></a>", $a);
        $a = str_replace(":sm263:", "<a id=\"smile\" class=\"smile-smile-_263_\"></a>", $a);
        $a = str_replace(":sm264:", "<a id=\"smile\" class=\"smile-smile-_264_\"></a>", $a);
        $a = str_replace(":sm265:", "<a id=\"smile\" class=\"smile-smile-_265_\"></a>", $a);
        $a = str_replace(":sm266:", "<a id=\"smile\" class=\"smile-smile-_266_\"></a>", $a);
        $a = str_replace(":sm267:", "<a id=\"smile\" class=\"smile-smile-_267_\"></a>", $a);
        $a = str_replace(":sm268:", "<a id=\"smile\" class=\"smile-smile-_268_\"></a>", $a);
        $a = str_replace(":sm269:", "<a id=\"smile\" class=\"smile-smile-_269_\"></a>", $a);
        $a = str_replace(":sm270:", "<a id=\"smile\" class=\"smile-smile-_270_\"></a>", $a);
        $a = str_replace(":sm271:", "<a id=\"smile\" class=\"smile-smile-_271_\"></a>", $a);
        $a = str_replace(":sm272:", "<a id=\"smile\" class=\"smile-smile-_272_\"></a>", $a);
        $a = str_replace(":sm273:", "<a id=\"smile\" class=\"smile-smile-_273_\"></a>", $a);
        $a = str_replace(":sm274:", "<a id=\"smile\" class=\"smile-smile-_274_\"></a>", $a);
        $a = str_replace(":sm275:", "<a id=\"smile\" class=\"smile-smile-_275_\"></a>", $a);
        $a = str_replace(":sm276:", "<a id=\"smile\" class=\"smile-smile-_276_\"></a>", $a);
        $a = str_replace(":sm277:", "<a id=\"smile\" class=\"smile-smile-_277_\"></a>", $a);
        $a = str_replace(":sm278:", "<a id=\"smile\" class=\"smile-smile-_278_\"></a>", $a);
        $a = str_replace(":sm279:", "<a id=\"smile\" class=\"smile-smile-_279_\"></a>", $a);
        $a = str_replace(":sm280:", "<a id=\"smile\" class=\"smile-smile-_280_\"></a>", $a);
        $a = str_replace(":sm281:", "<a id=\"smile\" class=\"smile-smile-_281_\"></a>", $a);
        $a = str_replace(":sm282:", "<a id=\"smile\" class=\"smile-smile-_282_\"></a>", $a);
        $a = str_replace(":sm283:", "<a id=\"smile\" class=\"smile-smile-_283_\"></a>", $a);
        $a = str_replace(":sm284:", "<a id=\"smile\" class=\"smile-smile-_284_\"></a>", $a);
        $a = str_replace(":sm285:", "<a id=\"smile\" class=\"smile-smile-_285_\"></a>", $a);
        $a = str_replace(":sm286:", "<a id=\"smile\" class=\"smile-smile-_286_\"></a>", $a);
        $a = str_replace(":sm287:", "<a id=\"smile\" class=\"smile-smile-_287_\"></a>", $a);
        $a = str_replace(":sm288:", "<a id=\"smile\" class=\"smile-smile-_288_\"></a>", $a);
        $a = str_replace(":sm289:", "<a id=\"smile\" class=\"smile-smile-_289_\"></a>", $a);
        $a = str_replace(":sm290:", "<a id=\"smile\" class=\"smile-smile-_290_\"></a>", $a);
        $a = str_replace(":sm291:", "<a id=\"smile\" class=\"smile-smile-_291_\"></a>", $a);
        $a = str_replace(":sm292:", "<a id=\"smile\" class=\"smile-smile-_292_\"></a>", $a);
        $a = str_replace(":sm293:", "<a id=\"smile\" class=\"smile-smile-_293_\"></a>", $a);
        $a = str_replace(":sm294:", "<a id=\"smile\" class=\"smile-smile-_294_\"></a>", $a);
        $a = str_replace(":sm295:", "<a id=\"smile\" class=\"smile-smile-_295_\"></a>", $a);
        $a = str_replace(":sm296:", "<a id=\"smile\" class=\"smile-smile-_296_\"></a>", $a);
        $a = str_replace(":sm297:", "<a id=\"smile\" class=\"smile-smile-_297_\"></a>", $a);
        $a = str_replace(":sm298:", "<a id=\"smile\" class=\"smile-smile-_298_\"></a>", $a);
        $a = str_replace(":sm299:", "<a id=\"smile\" class=\"smile-smile-_299_\"></a>", $a);
        $a = str_replace(":sm300:", "<a id=\"smile\" class=\"smile-smile-_300_\"></a>", $a);
        $a = str_replace(":sm301:", "<a id=\"smile\" class=\"smile-smile-_301_\"></a>", $a);
        $a = str_replace(":sm302:", "<a id=\"smile\" class=\"smile-smile-_302_\"></a>", $a);
        $a = str_replace(":sm303:", "<a id=\"smile\" class=\"smile-smile-_303_\"></a>", $a);
        $a = str_replace(":sm304:", "<a id=\"smile\" class=\"smile-smile-_304_\"></a>", $a);
        $a = str_replace(":sm305:", "<a id=\"smile\" class=\"smile-smile-_305_\"></a>", $a);
        $a = str_replace(":sm306:", "<a id=\"smile\" class=\"smile-smile-_306_\"></a>", $a);
        $a = str_replace(":sm307:", "<a id=\"smile\" class=\"smile-smile-_307_\"></a>", $a);
        $a = str_replace(":sm308:", "<a id=\"smile\" class=\"smile-smile-_308_\"></a>", $a);
        $a = str_replace(":sm309:", "<a id=\"smile\" class=\"smile-smile-_309_\"></a>", $a);
        $a = str_replace(":sm310:", "<a id=\"smile\" class=\"smile-smile-_310_\"></a>", $a);
        $a = str_replace(":sm311:", "<a id=\"smile\" class=\"smile-smile-_311_\"></a>", $a);
        $a = str_replace(":sm312:", "<a id=\"smile\" class=\"smile-smile-_312_\"></a>", $a);
        $a = str_replace(":sm313:", "<a id=\"smile\" class=\"smile-smile-_313_\"></a>", $a);
        $a = str_replace(":sm314:", "<a id=\"smile\" class=\"smile-smile-_314_\"></a>", $a);
        $a = str_replace(":sm315:", "<a id=\"smile\" class=\"smile-smile-_315_\"></a>", $a);
        $a = str_replace(":sm316:", "<a id=\"smile\" class=\"smile-smile-_316_\"></a>", $a);
        $a = str_replace(":sm317:", "<a id=\"smile\" class=\"smile-smile-_317_\"></a>", $a);
        $a = str_replace(":sm318:", "<a id=\"smile\" class=\"smile-smile-_318_\"></a>", $a);
        $a = str_replace(":sm319:", "<a id=\"smile\" class=\"smile-smile-_319_\"></a>", $a);
        $a = str_replace(":sm320:", "<a id=\"smile\" class=\"smile-smile-_320_\"></a>", $a);
        $a = str_replace(":sm321:", "<a id=\"smile\" class=\"smile-smile-_321_\"></a>", $a);
        $a = str_replace(":sm322:", "<a id=\"smile\" class=\"smile-smile-_322_\"></a>", $a);
        $a = str_replace(":sm323:", "<a id=\"smile\" class=\"smile-smile-_323_\"></a>", $a);
        $a = str_replace(":sm324:", "<a id=\"smile\" class=\"smile-smile-_324_\"></a>", $a);
        $a = str_replace(":sm325:", "<a id=\"smile\" class=\"smile-smile-_325_\"></a>", $a);
        $a = str_replace(":sm326:", "<a id=\"smile\" class=\"smile-smile-_326_\"></a>", $a);
        $a = str_replace(":sm327:", "<a id=\"smile\" class=\"smile-smile-_327_\"></a>", $a);
        $a = str_replace(":sm328:", "<a id=\"smile\" class=\"smile-smile-_328_\"></a>", $a);
        $a = str_replace(":sm329:", "<a id=\"smile\" class=\"smile-smile-_329_\"></a>", $a);
        $a = str_replace(":sm330:", "<a id=\"smile\" class=\"smile-smile-_330_\"></a>", $a);
        $a = str_replace(":sm331:", "<a id=\"smile\" class=\"smile-smile-_331_\"></a>", $a);
        $a = str_replace(":sm332:", "<a id=\"smile\" class=\"smile-smile-_332_\"></a>", $a);
        $a = str_replace(":sm333:", "<a id=\"smile\" class=\"smile-smile-_333_\"></a>", $a);
        $a = str_replace(":sm334:", "<a id=\"smile\" class=\"smile-smile-_334_\"></a>", $a);
        $a = str_replace(":sm335:", "<a id=\"smile\" class=\"smile-smile-_335_\"></a>", $a);
        $a = str_replace(":sm336:", "<a id=\"smile\" class=\"smile-smile-_336_\"></a>", $a);
        $a = str_replace(":sm337:", "<a id=\"smile\" class=\"smile-smile-_337_\"></a>", $a);
        $a = str_replace(":sm338:", "<a id=\"smile\" class=\"smile-smile-_338_\"></a>", $a);
        $a = str_replace(":sm339:", "<a id=\"smile\" class=\"smile-smile-_339_\"></a>", $a);
        $a = str_replace(":sm340:", "<a id=\"smile\" class=\"smile-smile-_340_\"></a>", $a);
        $a = str_replace(":sm341:", "<a id=\"smile\" class=\"smile-smile-_341_\"></a>", $a);
        $a = str_replace(":sm342:", "<a id=\"smile\" class=\"smile-smile-_342_\"></a>", $a);
        $a = str_replace(":sm343:", "<a id=\"smile\" class=\"smile-smile-_343_\"></a>", $a);
        $a = str_replace(":sm344:", "<a id=\"smile\" class=\"smile-smile-_344_\"></a>", $a);
        $a = str_replace(":sm345:", "<a id=\"smile\" class=\"smile-smile-_345_\"></a>", $a);
        $a = str_replace(":sm346:", "<a id=\"smile\" class=\"smile-smile-_346_\"></a>", $a);
        $a = str_replace(":sm347:", "<a id=\"smile\" class=\"smile-smile-_347_\"></a>", $a);
        $a = str_replace(":sm348:", "<a id=\"smile\" class=\"smile-smile-_348_\"></a>", $a);
        $a = str_replace(":sm349:", "<a id=\"smile\" class=\"smile-smile-_349_\"></a>", $a);
        $a = str_replace(":sm350:", "<a id=\"smile\" class=\"smile-smile-_350_\"></a>", $a);
        $a = str_replace(":sm351:", "<a id=\"smile\" class=\"smile-smile-_351_\"></a>", $a);
        $a = str_replace(":sm352:", "<a id=\"smile\" class=\"smile-smile-_352_\"></a>", $a);
        $a = str_replace(":sm353:", "<a id=\"smile\" class=\"smile-smile-_353_\"></a>", $a);
        $a = str_replace(":sm354:", "<a id=\"smile\" class=\"smile-smile-_354_\"></a>", $a);
        $a = str_replace(":sm355:", "<a id=\"smile\" class=\"smile-smile-_355_\"></a>", $a);
        $a = str_replace(":sm356:", "<a id=\"smile\" class=\"smile-smile-_356_\"></a>", $a);
        $a = str_replace(":sm357:", "<a id=\"smile\" class=\"smile-smile-_357_\"></a>", $a);
        $a = str_replace(":sm358:", "<a id=\"smile\" class=\"smile-smile-_358_\"></a>", $a);
        $a = str_replace(":sm359:", "<a id=\"smile\" class=\"smile-smile-_359_\"></a>", $a);
        $a = str_replace(":sm360:", "<a id=\"smile\" class=\"smile-smile-_360_\"></a>", $a);
        $a = str_replace(":sm361:", "<a id=\"smile\" class=\"smile-smile-_361_\"></a>", $a);
        $a = str_replace(":sm362:", "<a id=\"smile\" class=\"smile-smile-_362_\"></a>", $a);
        $a = str_replace(":sm363:", "<a id=\"smile\" class=\"smile-smile-_363_\"></a>", $a);
        $a = str_replace(":sm364:", "<a id=\"smile\" class=\"smile-smile-_364_\"></a>", $a);
        $a = str_replace(":sm365:", "<a id=\"smile\" class=\"smile-smile-_365_\"></a>", $a);
        $a = str_replace(":sm366:", "<a id=\"smile\" class=\"smile-smile-_366_\"></a>", $a);
        $a = str_replace(":sm367:", "<a id=\"smile\" class=\"smile-smile-_367_\"></a>", $a);
        $a = str_replace(":sm368:", "<a id=\"smile\" class=\"smile-smile-_368_\"></a>", $a);
        $a = str_replace(":sm369:", "<a id=\"smile\" class=\"smile-smile-_369_\"></a>", $a);
        $a = str_replace(":sm370:", "<a id=\"smile\" class=\"smile-smile-_370_\"></a>", $a);
        $a = str_replace(":sm371:", "<a id=\"smile\" class=\"smile-smile-_371_\"></a>", $a);
        $a = str_replace(":sm372:", "<a id=\"smile\" class=\"smile-smile-_372_\"></a>", $a);
        $a = str_replace(":sm373:", "<a id=\"smile\" class=\"smile-smile-_373_\"></a>", $a);
        $a = str_replace(":sm374:", "<a id=\"smile\" class=\"smile-smile-_374_\"></a>", $a);
        $a = str_replace(":sm375:", "<a id=\"smile\" class=\"smile-smile-_375_\"></a>", $a);
        $a = str_replace(":sm376:", "<a id=\"smile\" class=\"smile-smile-_376_\"></a>", $a);
        $a = str_replace(":sm377:", "<a id=\"smile\" class=\"smile-smile-_377_\"></a>", $a);
        $a = str_replace(":sm378:", "<a id=\"smile\" class=\"smile-smile-_378_\"></a>", $a);
        $a = str_replace(":sm379:", "<a id=\"smile\" class=\"smile-smile-_379_\"></a>", $a);
        $a = str_replace(":sm380:", "<a id=\"smile\" class=\"smile-smile-_380_\"></a>", $a);
        $a = str_replace(":sm381:", "<a id=\"smile\" class=\"smile-smile-_381_\"></a>", $a);
        $a = str_replace(":sm382:", "<a id=\"smile\" class=\"smile-smile-_382_\"></a>", $a);
        $a = str_replace(":sm383:", "<a id=\"smile\" class=\"smile-smile-_383_\"></a>", $a);
        $a = str_replace(":sm384:", "<a id=\"smile\" class=\"smile-smile-_384_\"></a>", $a);
        $a = str_replace(":sm385:", "<a id=\"smile\" class=\"smile-smile-_385_\"></a>", $a);
        $a = str_replace(":sm386:", "<a id=\"smile\" class=\"smile-smile-_386_\"></a>", $a);
        $a = str_replace(":sm387:", "<a id=\"smile\" class=\"smile-smile-_387_\"></a>", $a);
        $a = str_replace(":sm388:", "<a id=\"smile\" class=\"smile-smile-_388_\"></a>", $a);
        $a = str_replace(":sm389:", "<a id=\"smile\" class=\"smile-smile-_389_\"></a>", $a);
        $a = str_replace(":sm390:", "<a id=\"smile\" class=\"smile-smile-_390_\"></a>", $a);
        $a = str_replace(":sm391:", "<a id=\"smile\" class=\"smile-smile-_391_\"></a>", $a);
        $a = str_replace(":sm392:", "<a id=\"smile\" class=\"smile-smile-_392_\"></a>", $a);
        $a = str_replace(":sm393:", "<a id=\"smile\" class=\"smile-smile-_393_\"></a>", $a);
        $a = str_replace(":sm394:", "<a id=\"smile\" class=\"smile-smile-_394_\"></a>", $a);
        $a = str_replace(":sm395:", "<a id=\"smile\" class=\"smile-smile-_395_\"></a>", $a);
        $a = str_replace(":sm396:", "<a id=\"smile\" class=\"smile-smile-_396_\"></a>", $a);
        $a = str_replace(":sm397:", "<a id=\"smile\" class=\"smile-smile-_397_\"></a>", $a);
        $a = str_replace(":sm398:", "<a id=\"smile\" class=\"smile-smile-_398_\"></a>", $a);
        $a = str_replace(":sm399:", "<a id=\"smile\" class=\"smile-smile-_399_\"></a>", $a);
        $a = str_replace(":sm400:", "<a id=\"smile\" class=\"smile-smile-_400_\"></a>", $a);
        $a = str_replace(":sm401:", "<a id=\"smile\" class=\"smile-smile-_401_\"></a>", $a);
        $a = str_replace(":sm402:", "<a id=\"smile\" class=\"smile-smile-_402_\"></a>", $a);
        $a = str_replace(":sm403:", "<a id=\"smile\" class=\"smile-smile-_403_\"></a>", $a);
        $a = str_replace(":sm404:", "<a id=\"smile\" class=\"smile-smile-_404_\"></a>", $a);
        $a = str_replace(":sm405:", "<a id=\"smile\" class=\"smile-smile-_405_\"></a>", $a);
        $a = str_replace(":sm406:", "<a id=\"smile\" class=\"smile-smile-_406_\"></a>", $a);
        $a = str_replace(":sm407:", "<a id=\"smile\" class=\"smile-smile-_407_\"></a>", $a);
        $a = str_replace(":sm408:", "<a id=\"smile\" class=\"smile-smile-_408_\"></a>", $a);
        $a = str_replace(":sm409:", "<a id=\"smile\" class=\"smile-smile-_409_\"></a>", $a);
        $a = str_replace(":sm410:", "<a id=\"smile\" class=\"smile-smile-_410_\"></a>", $a);
        $a = str_replace(":sm411:", "<a id=\"smile\" class=\"smile-smile-_411_\"></a>", $a);
        $a = str_replace(":sm412:", "<a id=\"smile\" class=\"smile-smile-_412_\"></a>", $a);
        $a = str_replace(":sm413:", "<a id=\"smile\" class=\"smile-smile-_413_\"></a>", $a);
        $a = str_replace(":sm414:", "<a id=\"smile\" class=\"smile-smile-_414_\"></a>", $a);
        $a = str_replace(":sm415:", "<a id=\"smile\" class=\"smile-smile-_415_\"></a>", $a);
        $a = str_replace(":sm416:", "<a id=\"smile\" class=\"smile-smile-_416_\"></a>", $a);
        $a = str_replace(":sm417:", "<a id=\"smile\" class=\"smile-smile-_417_\"></a>", $a);
        $a = str_replace(":sm418:", "<a id=\"smile\" class=\"smile-smile-_418_\"></a>", $a);
        $a = str_replace(":sm419:", "<a id=\"smile\" class=\"smile-smile-_419_\"></a>", $a);
        $a = str_replace(":sm420:", "<a id=\"smile\" class=\"smile-smile-_420_\"></a>", $a);
        $a = str_replace(":sm421:", "<a id=\"smile\" class=\"smile-smile-_421_\"></a>", $a);
        $a = str_replace(":sm422:", "<a id=\"smile\" class=\"smile-smile-_422_\"></a>", $a);
        $a = str_replace(":sm423:", "<a id=\"smile\" class=\"smile-smile-_423_\"></a>", $a);
        $a = str_replace(":sm424:", "<a id=\"smile\" class=\"smile-smile-_424_\"></a>", $a);
        $a = str_replace(":sm425:", "<a id=\"smile\" class=\"smile-smile-_425_\"></a>", $a);
        $a = str_replace(":sm426:", "<a id=\"smile\" class=\"smile-smile-_426_\"></a>", $a);
        $a = str_replace(":sm427:", "<a id=\"smile\" class=\"smile-smile-_427_\"></a>", $a);
        $a = str_replace(":sm428:", "<a id=\"smile\" class=\"smile-smile-_428_\"></a>", $a);
        $a = str_replace(":sm429:", "<a id=\"smile\" class=\"smile-smile-_429_\"></a>", $a);
        $a = str_replace(":sm430:", "<a id=\"smile\" class=\"smile-smile-_430_\"></a>", $a);
        $a = str_replace(":sm431:", "<a id=\"smile\" class=\"smile-smile-_431_\"></a>", $a);
        $a = str_replace(":sm432:", "<a id=\"smile\" class=\"smile-smile-_432_\"></a>", $a);
        $a = str_replace(":sm433:", "<a id=\"smile\" class=\"smile-smile-_433_\"></a>", $a);
        $a = str_replace(":sm434:", "<a id=\"smile\" class=\"smile-smile-_434_\"></a>", $a);
        $a = str_replace(":sm435:", "<a id=\"smile\" class=\"smile-smile-_435_\"></a>", $a);
        $a = str_replace(":sm436:", "<a id=\"smile\" class=\"smile-smile-_436_\"></a>", $a);
        $a = str_replace(":sm437:", "<a id=\"smile\" class=\"smile-smile-_437_\"></a>", $a);
        $a = str_replace(":sm438:", "<a id=\"smile\" class=\"smile-smile-_438_\"></a>", $a);
        $a = str_replace(":sm439:", "<a id=\"smile\" class=\"smile-smile-_439_\"></a>", $a);
        $a = str_replace(":sm440:", "<a id=\"smile\" class=\"smile-smile-_440_\"></a>", $a);
        $a = str_replace(":sm441:", "<a id=\"smile\" class=\"smile-smile-_441_\"></a>", $a);
        $a = str_replace(":sm442:", "<a id=\"smile\" class=\"smile-smile-_442_\"></a>", $a);
        $a = str_replace(":sm443:", "<a id=\"smile\" class=\"smile-smile-_443_\"></a>", $a);
        $a = str_replace(":sm444:", "<a id=\"smile\" class=\"smile-smile-_444_\"></a>", $a);
        $a = str_replace(":sm445:", "<a id=\"smile\" class=\"smile-smile-_445_\"></a>", $a);
        $a = str_replace(":sm446:", "<a id=\"smile\" class=\"smile-smile-_446_\"></a>", $a);
        $a = str_replace(":sm447:", "<a id=\"smile\" class=\"smile-smile-_447_\"></a>", $a);
        $a = str_replace(":sm448:", "<a id=\"smile\" class=\"smile-smile-_448_\"></a>", $a);
        $a = str_replace(":sm449:", "<a id=\"smile\" class=\"smile-smile-_449_\"></a>", $a);
        $a = str_replace(":sm450:", "<a id=\"smile\" class=\"smile-smile-_450_\"></a>", $a);
        $a = str_replace(":sm451:", "<a id=\"smile\" class=\"smile-smile-_451_\"></a>", $a);
        $a = str_replace(":sm452:", "<a id=\"smile\" class=\"smile-smile-_452_\"></a>", $a);
        $a = str_replace(":sm453:", "<a id=\"smile\" class=\"smile-smile-_453_\"></a>", $a);
        $a = str_replace(":sm454:", "<a id=\"smile\" class=\"smile-smile-_454_\"></a>", $a);
        $a = str_replace(":sm455:", "<a id=\"smile\" class=\"smile-smile-_455_\"></a>", $a);
        $a = str_replace(":sm456:", "<a id=\"smile\" class=\"smile-smile-_456_\"></a>", $a);
        $a = str_replace(":sm457:", "<a id=\"smile\" class=\"smile-smile-_457_\"></a>", $a);
        $a = str_replace(":sm458:", "<a id=\"smile\" class=\"smile-smile-_458_\"></a>", $a);
        $a = str_replace(":sm459:", "<a id=\"smile\" class=\"smile-smile-_459_\"></a>", $a);
        $a = str_replace(":sm460:", "<a id=\"smile\" class=\"smile-smile-_460_\"></a>", $a);
        $a = str_replace(":sm461:", "<a id=\"smile\" class=\"smile-smile-_461_\"></a>", $a);
        $a = str_replace(":sm462:", "<a id=\"smile\" class=\"smile-smile-_462_\"></a>", $a);
        $a = str_replace(":sm463:", "<a id=\"smile\" class=\"smile-smile-_463_\"></a>", $a);
        $a = str_replace(":sm464:", "<a id=\"smile\" class=\"smile-smile-_464_\"></a>", $a);
        $a = str_replace(":sm465:", "<a id=\"smile\" class=\"smile-smile-_465_\"></a>", $a);
        $a = str_replace(":sm466:", "<a id=\"smile\" class=\"smile-smile-_466_\"></a>", $a);
        $a = str_replace(":sm467:", "<a id=\"smile\" class=\"smile-smile-_467_\"></a>", $a);
        $a = str_replace(":sm468:", "<a id=\"smile\" class=\"smile-smile-_468_\"></a>", $a);
        $a = str_replace(":sm469:", "<a id=\"smile\" class=\"smile-smile-_469_\"></a>", $a);
        $a = str_replace(":sm470:", "<a id=\"smile\" class=\"smile-smile-_470_\"></a>", $a);
        $a = str_replace(":sm471:", "<a id=\"smile\" class=\"smile-smile-_471_\"></a>", $a);
        $a = str_replace(":sm472:", "<a id=\"smile\" class=\"smile-smile-_472_\"></a>", $a);
        $a = str_replace(":sm473:", "<a id=\"smile\" class=\"smile-smile-_473_\"></a>", $a);
        $a = str_replace(":sm474:", "<a id=\"smile\" class=\"smile-smile-_474_\"></a>", $a);
        $a = str_replace(":sm475:", "<a id=\"smile\" class=\"smile-smile-_475_\"></a>", $a);
        $a = str_replace(":sm476:", "<a id=\"smile\" class=\"smile-smile-_476_\"></a>", $a);
        $a = str_replace(":sm477:", "<a id=\"smile\" class=\"smile-smile-_477_\"></a>", $a);
        $a = str_replace(":sm478:", "<a id=\"smile\" class=\"smile-smile-_478_\"></a>", $a);
        $a = str_replace(":sm479:", "<a id=\"smile\" class=\"smile-smile-_479_\"></a>", $a);
        $a = str_replace(":sm480:", "<a id=\"smile\" class=\"smile-smile-_480_\"></a>", $a);
        $a = str_replace(":sm481:", "<a id=\"smile\" class=\"smile-smile-_481_\"></a>", $a);
        $a = str_replace(":sm482:", "<a id=\"smile\" class=\"smile-smile-_482_\"></a>", $a);
        $a = str_replace(":sm483:", "<a id=\"smile\" class=\"smile-smile-_483_\"></a>", $a);
        $a = str_replace(":sm484:", "<a id=\"smile\" class=\"smile-smile-_484_\"></a>", $a);
        $a = str_replace(":sm485:", "<a id=\"smile\" class=\"smile-smile-_485_\"></a>", $a);
        $a = str_replace(":sm486:", "<a id=\"smile\" class=\"smile-smile-_486_\"></a>", $a);
        $a = str_replace(":sm487:", "<a id=\"smile\" class=\"smile-smile-_487_\"></a>", $a);
        $a = str_replace(":sm488:", "<a id=\"smile\" class=\"smile-smile-_488_\"></a>", $a);
        $a = str_replace(":sm489:", "<a id=\"smile\" class=\"smile-smile-_489_\"></a>", $a);
        $a = str_replace(":sm490:", "<a id=\"smile\" class=\"smile-smile-_490_\"></a>", $a);
        $a = str_replace(":sm491:", "<a id=\"smile\" class=\"smile-smile-_491_\"></a>", $a);
        $a = str_replace(":sm492:", "<a id=\"smile\" class=\"smile-smile-_492_\"></a>", $a);
        $a = str_replace(":sm493:", "<a id=\"smile\" class=\"smile-smile-_493_\"></a>", $a);
        $a = str_replace(":sm494:", "<a id=\"smile\" class=\"smile-smile-_494_\"></a>", $a);
        $a = str_replace(":sm495:", "<a id=\"smile\" class=\"smile-smile-_495_\"></a>", $a);
        $a = str_replace(":sm496:", "<a id=\"smile\" class=\"smile-smile-_496_\"></a>", $a);
        $a = str_replace(":sm497:", "<a id=\"smile\" class=\"smile-smile-_497_\"></a>", $a);
        $a = str_replace(":sm498:", "<a id=\"smile\" class=\"smile-smile-_498_\"></a>", $a);
        $a = str_replace(":sm499:", "<a id=\"smile\" class=\"smile-smile-_499_\"></a>", $a);
        $a = str_replace(":sm500:", "<a id=\"smile\" class=\"smile-smile-_500_\"></a>", $a);
        $a = str_replace(":sm501:", "<a id=\"smile\" class=\"smile-smile-_501_\"></a>", $a);
        $a = str_replace(":sm502:", "<a id=\"smile\" class=\"smile-smile-_502_\"></a>", $a);
        $a = str_replace(":sm503:", "<a id=\"smile\" class=\"smile-smile-_503_\"></a>", $a);
        $a = str_replace(":sm504:", "<a id=\"smile\" class=\"smile-smile-_504_\"></a>", $a);
        $a = str_replace(":sm505:", "<a id=\"smile\" class=\"smile-smile-_505_\"></a>", $a);
        return $a;
    }
    public function chat(Request $request){
		$returnValue = [];
        $max = $this->redis->llen(self::CHAT_CHANNEL);
		$min = 0;
		if ($max > config('mod_game.chat_history_length')) $min = $max - config('mod_game.chat_history_length');
        $value = $this->redis->lrange(self::CHAT_CHANNEL, $min, $max);
        $i = 0;
        foreach ($value as $key => $newchat[$i]) {
            $a = json_decode($newchat[$i], true);
            $a['username'] = htmlspecialchars($a['username']);
            $a["messages"]  = self::replaceSmile($a["messages"]);
            $returnValue[$i] = [
				'id' => $min + $i,
                'userid' => $a['userid'],
                'avatar' => $a['avatar'],
                'time' => $a['time'],
                'messages' => $a['messages'],
                'username' => $a['username'],
				'moder' => $a['moder'],
				'vip' => $a['vip'],
                'admin' => $a['admin']
			];
            $i++;
        }
        return $returnValue;
    }
	
    public function delmsg(Request $request){
		$id = $request->get('id');
        $value = $this->redis->lrange(self::CHAT_CHANNEL, $id, $id);
		$i = 0;
        foreach ($value as $key => $newchat) {
            $a = json_decode($newchat, true);
			$msg = $newchat;
			$userid = $a['userid'];
			$i++;
        }
		if($i > 0){
			if ($userid == $this->user->steamid64 || $this->user->is_admin == 1 || $this->user->is_moderator == 1 ){
				$this->redis->lrem(self::CHAT_CHANNEL, -1, $msg);
				$this->redis->publish(self::NEW_MSG_CHANNEL, json_encode(1));
				return response()->json(['message' => 'Сообщение успешно удалено', 'status' => 'success']);
			} else {
				return response()->json(['message' => 'Вы не можете удалять чужие сообщения', 'status' => 'error']);
			}
		} else {
			return response()->json(['message' => 'Сообщение не существует', 'status' => 'error']);
		}
    }

    private function _responseMessageToSite($message, $userid){
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
}
