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
        $a = str_replace(":sm1:", "<img id=\"smile\" class=\"smile-smile-_1_\">", $a);
        $a = str_replace(":sm2:", "<img id=\"smile\" class=\"smile-smile-_2_\">", $a);
        $a = str_replace(":sm3:", "<img id=\"smile\" class=\"smile-smile-_3_\">", $a);
        $a = str_replace(":sm4:", "<img id=\"smile\" class=\"smile-smile-_4_\">", $a);
        $a = str_replace(":sm5:", "<img id=\"smile\" class=\"smile-smile-_5_\">", $a);
        $a = str_replace(":sm6:", "<img id=\"smile\" class=\"smile-smile-_6_\">", $a);
        $a = str_replace(":sm7:", "<img id=\"smile\" class=\"smile-smile-_7_\">", $a);
        $a = str_replace(":sm8:", "<img id=\"smile\" class=\"smile-smile-_8_\">", $a);
        $a = str_replace(":sm9:", "<img id=\"smile\" class=\"smile-smile-_9_\">", $a);
        $a = str_replace(":sm10:", "<img id=\"smile\" class=\"smile-smile-_10_\">", $a);
        $a = str_replace(":sm11:", "<img id=\"smile\" class=\"smile-smile-_11_\">", $a);
        $a = str_replace(":sm12:", "<img id=\"smile\" class=\"smile-smile-_12_\">", $a);
        $a = str_replace(":sm13:", "<img id=\"smile\" class=\"smile-smile-_13_\">", $a);
        $a = str_replace(":sm14:", "<img id=\"smile\" class=\"smile-smile-_14_\">", $a);
        $a = str_replace(":sm15:", "<img id=\"smile\" class=\"smile-smile-_15_\">", $a);
        $a = str_replace(":sm16:", "<img id=\"smile\" class=\"smile-smile-_16_\">", $a);
        $a = str_replace(":sm17:", "<img id=\"smile\" class=\"smile-smile-_17_\">", $a);
        $a = str_replace(":sm18:", "<img id=\"smile\" class=\"smile-smile-_18_\">", $a);
        $a = str_replace(":sm19:", "<img id=\"smile\" class=\"smile-smile-_19_\">", $a);
        $a = str_replace(":sm20:", "<img id=\"smile\" class=\"smile-smile-_20_\">", $a);
        $a = str_replace(":sm21:", "<img id=\"smile\" class=\"smile-smile-_21_\">", $a);
        $a = str_replace(":sm22:", "<img id=\"smile\" class=\"smile-smile-_22_\">", $a);
        $a = str_replace(":sm23:", "<img id=\"smile\" class=\"smile-smile-_23_\">", $a);
        $a = str_replace(":sm24:", "<img id=\"smile\" class=\"smile-smile-_24_\">", $a);
        $a = str_replace(":sm25:", "<img id=\"smile\" class=\"smile-smile-_25_\">", $a);
        $a = str_replace(":sm26:", "<img id=\"smile\" class=\"smile-smile-_26_\">", $a);
        $a = str_replace(":sm27:", "<img id=\"smile\" class=\"smile-smile-_27_\">", $a);
        $a = str_replace(":sm28:", "<img id=\"smile\" class=\"smile-smile-_28_\">", $a);
        $a = str_replace(":sm29:", "<img id=\"smile\" class=\"smile-smile-_29_\">", $a);
        $a = str_replace(":sm30:", "<img id=\"smile\" class=\"smile-smile-_30_\">", $a);
        $a = str_replace(":sm31:", "<img id=\"smile\" class=\"smile-smile-_31_\">", $a);
        $a = str_replace(":sm32:", "<img id=\"smile\" class=\"smile-smile-_32_\">", $a);
        $a = str_replace(":sm33:", "<img id=\"smile\" class=\"smile-smile-_33_\">", $a);
        $a = str_replace(":sm34:", "<img id=\"smile\" class=\"smile-smile-_34_\">", $a);
        $a = str_replace(":sm35:", "<img id=\"smile\" class=\"smile-smile-_35_\">", $a);
        $a = str_replace(":sm36:", "<img id=\"smile\" class=\"smile-smile-_36_\">", $a);
        $a = str_replace(":sm37:", "<img id=\"smile\" class=\"smile-smile-_37_\">", $a);
        $a = str_replace(":sm38:", "<img id=\"smile\" class=\"smile-smile-_38_\">", $a);
        $a = str_replace(":sm39:", "<img id=\"smile\" class=\"smile-smile-_39_\">", $a);
        $a = str_replace(":sm40:", "<img id=\"smile\" class=\"smile-smile-_40_\">", $a);
        $a = str_replace(":sm41:", "<img id=\"smile\" class=\"smile-smile-_41_\">", $a);
        $a = str_replace(":sm42:", "<img id=\"smile\" class=\"smile-smile-_42_\">", $a);
        $a = str_replace(":sm43:", "<img id=\"smile\" class=\"smile-smile-_43_\">", $a);
        $a = str_replace(":sm44:", "<img id=\"smile\" class=\"smile-smile-_44_\">", $a);
        $a = str_replace(":sm45:", "<img id=\"smile\" class=\"smile-smile-_45_\">", $a);
        $a = str_replace(":sm46:", "<img id=\"smile\" class=\"smile-smile-_46_\">", $a);
        $a = str_replace(":sm47:", "<img id=\"smile\" class=\"smile-smile-_47_\">", $a);
        $a = str_replace(":sm48:", "<img id=\"smile\" class=\"smile-smile-_48_\">", $a);
        $a = str_replace(":sm49:", "<img id=\"smile\" class=\"smile-smile-_49_\">", $a);
        $a = str_replace(":sm50:", "<img id=\"smile\" class=\"smile-smile-_50_\">", $a);
        $a = str_replace(":sm51:", "<img id=\"smile\" class=\"smile-smile-_51_\">", $a);
        $a = str_replace(":sm52:", "<img id=\"smile\" class=\"smile-smile-_52_\">", $a);
        $a = str_replace(":sm53:", "<img id=\"smile\" class=\"smile-smile-_53_\">", $a);
        $a = str_replace(":sm54:", "<img id=\"smile\" class=\"smile-smile-_54_\">", $a);
        $a = str_replace(":sm55:", "<img id=\"smile\" class=\"smile-smile-_55_\">", $a);
        $a = str_replace(":sm56:", "<img id=\"smile\" class=\"smile-smile-_56_\">", $a);
        $a = str_replace(":sm57:", "<img id=\"smile\" class=\"smile-smile-_57_\">", $a);
        $a = str_replace(":sm58:", "<img id=\"smile\" class=\"smile-smile-_58_\">", $a);
        $a = str_replace(":sm59:", "<img id=\"smile\" class=\"smile-smile-_59_\">", $a);
        $a = str_replace(":sm60:", "<img id=\"smile\" class=\"smile-smile-_60_\">", $a);
        $a = str_replace(":sm61:", "<img id=\"smile\" class=\"smile-smile-_61_\">", $a);
        $a = str_replace(":sm62:", "<img id=\"smile\" class=\"smile-smile-_62_\">", $a);
        $a = str_replace(":sm63:", "<img id=\"smile\" class=\"smile-smile-_63_\">", $a);
        $a = str_replace(":sm64:", "<img id=\"smile\" class=\"smile-smile-_64_\">", $a);
        $a = str_replace(":sm65:", "<img id=\"smile\" class=\"smile-smile-_65_\">", $a);
        $a = str_replace(":sm66:", "<img id=\"smile\" class=\"smile-smile-_66_\">", $a);
        $a = str_replace(":sm67:", "<img id=\"smile\" class=\"smile-smile-_67_\">", $a);
        $a = str_replace(":sm68:", "<img id=\"smile\" class=\"smile-smile-_68_\">", $a);
        $a = str_replace(":sm69:", "<img id=\"smile\" class=\"smile-smile-_69_\">", $a);
        $a = str_replace(":sm70:", "<img id=\"smile\" class=\"smile-smile-_70_\">", $a);
        $a = str_replace(":sm71:", "<img id=\"smile\" class=\"smile-smile-_71_\">", $a);
        $a = str_replace(":sm72:", "<img id=\"smile\" class=\"smile-smile-_72_\">", $a);
        $a = str_replace(":sm73:", "<img id=\"smile\" class=\"smile-smile-_73_\">", $a);
        $a = str_replace(":sm74:", "<img id=\"smile\" class=\"smile-smile-_74_\">", $a);
        $a = str_replace(":sm75:", "<img id=\"smile\" class=\"smile-smile-_75_\">", $a);
        $a = str_replace(":sm76:", "<img id=\"smile\" class=\"smile-smile-_76_\">", $a);
        $a = str_replace(":sm77:", "<img id=\"smile\" class=\"smile-smile-_77_\">", $a);
        $a = str_replace(":sm78:", "<img id=\"smile\" class=\"smile-smile-_78_\">", $a);
        $a = str_replace(":sm79:", "<img id=\"smile\" class=\"smile-smile-_79_\">", $a);
        $a = str_replace(":sm80:", "<img id=\"smile\" class=\"smile-smile-_80_\">", $a);
        $a = str_replace(":sm81:", "<img id=\"smile\" class=\"smile-smile-_81_\">", $a);
        $a = str_replace(":sm82:", "<img id=\"smile\" class=\"smile-smile-_82_\">", $a);
        $a = str_replace(":sm83:", "<img id=\"smile\" class=\"smile-smile-_83_\">", $a);
        $a = str_replace(":sm84:", "<img id=\"smile\" class=\"smile-smile-_84_\">", $a);
        $a = str_replace(":sm85:", "<img id=\"smile\" class=\"smile-smile-_85_\">", $a);
        $a = str_replace(":sm86:", "<img id=\"smile\" class=\"smile-smile-_86_\">", $a);
        $a = str_replace(":sm87:", "<img id=\"smile\" class=\"smile-smile-_87_\">", $a);
        $a = str_replace(":sm88:", "<img id=\"smile\" class=\"smile-smile-_88_\">", $a);
        $a = str_replace(":sm89:", "<img id=\"smile\" class=\"smile-smile-_89_\">", $a);
        $a = str_replace(":sm90:", "<img id=\"smile\" class=\"smile-smile-_90_\">", $a);
        $a = str_replace(":sm91:", "<img id=\"smile\" class=\"smile-smile-_91_\">", $a);
        $a = str_replace(":sm92:", "<img id=\"smile\" class=\"smile-smile-_92_\">", $a);
        $a = str_replace(":sm93:", "<img id=\"smile\" class=\"smile-smile-_93_\">", $a);
        $a = str_replace(":sm94:", "<img id=\"smile\" class=\"smile-smile-_94_\">", $a);
        $a = str_replace(":sm95:", "<img id=\"smile\" class=\"smile-smile-_95_\">", $a);
        $a = str_replace(":sm96:", "<img id=\"smile\" class=\"smile-smile-_96_\">", $a);
        $a = str_replace(":sm97:", "<img id=\"smile\" class=\"smile-smile-_97_\">", $a);
        $a = str_replace(":sm98:", "<img id=\"smile\" class=\"smile-smile-_98_\">", $a);
        $a = str_replace(":sm99:", "<img id=\"smile\" class=\"smile-smile-_99_\">", $a);
        $a = str_replace(":sm100:", "<img id=\"smile\" class=\"smile-smile-_100_\">", $a);
        $a = str_replace(":sm101:", "<img id=\"smile\" class=\"smile-smile-_101_\">", $a);
        $a = str_replace(":sm102:", "<img id=\"smile\" class=\"smile-smile-_102_\">", $a);
        $a = str_replace(":sm103:", "<img id=\"smile\" class=\"smile-smile-_103_\">", $a);
        $a = str_replace(":sm104:", "<img id=\"smile\" class=\"smile-smile-_104_\">", $a);
        $a = str_replace(":sm105:", "<img id=\"smile\" class=\"smile-smile-_105_\">", $a);
        $a = str_replace(":sm106:", "<img id=\"smile\" class=\"smile-smile-_106_\">", $a);
        $a = str_replace(":sm107:", "<img id=\"smile\" class=\"smile-smile-_107_\">", $a);
        $a = str_replace(":sm108:", "<img id=\"smile\" class=\"smile-smile-_108_\">", $a);
        $a = str_replace(":sm109:", "<img id=\"smile\" class=\"smile-smile-_109_\">", $a);
        $a = str_replace(":sm110:", "<img id=\"smile\" class=\"smile-smile-_110_\">", $a);
        $a = str_replace(":sm111:", "<img id=\"smile\" class=\"smile-smile-_111_\">", $a);
        $a = str_replace(":sm112:", "<img id=\"smile\" class=\"smile-smile-_112_\">", $a);
        $a = str_replace(":sm113:", "<img id=\"smile\" class=\"smile-smile-_113_\">", $a);
        $a = str_replace(":sm114:", "<img id=\"smile\" class=\"smile-smile-_114_\">", $a);
        $a = str_replace(":sm115:", "<img id=\"smile\" class=\"smile-smile-_115_\">", $a);
        $a = str_replace(":sm116:", "<img id=\"smile\" class=\"smile-smile-_116_\">", $a);
        $a = str_replace(":sm117:", "<img id=\"smile\" class=\"smile-smile-_117_\">", $a);
        $a = str_replace(":sm118:", "<img id=\"smile\" class=\"smile-smile-_118_\">", $a);
        $a = str_replace(":sm119:", "<img id=\"smile\" class=\"smile-smile-_119_\">", $a);
        $a = str_replace(":sm120:", "<img id=\"smile\" class=\"smile-smile-_120_\">", $a);
        $a = str_replace(":sm121:", "<img id=\"smile\" class=\"smile-smile-_121_\">", $a);
        $a = str_replace(":sm122:", "<img id=\"smile\" class=\"smile-smile-_122_\">", $a);
        $a = str_replace(":sm123:", "<img id=\"smile\" class=\"smile-smile-_123_\">", $a);
        $a = str_replace(":sm124:", "<img id=\"smile\" class=\"smile-smile-_124_\">", $a);
        $a = str_replace(":sm125:", "<img id=\"smile\" class=\"smile-smile-_125_\">", $a);
        $a = str_replace(":sm126:", "<img id=\"smile\" class=\"smile-smile-_126_\">", $a);
        $a = str_replace(":sm127:", "<img id=\"smile\" class=\"smile-smile-_127_\">", $a);
        $a = str_replace(":sm128:", "<img id=\"smile\" class=\"smile-smile-_128_\">", $a);
        $a = str_replace(":sm129:", "<img id=\"smile\" class=\"smile-smile-_129_\">", $a);
        $a = str_replace(":sm130:", "<img id=\"smile\" class=\"smile-smile-_130_\">", $a);
        $a = str_replace(":sm131:", "<img id=\"smile\" class=\"smile-smile-_131_\">", $a);
        $a = str_replace(":sm132:", "<img id=\"smile\" class=\"smile-smile-_132_\">", $a);
        $a = str_replace(":sm133:", "<img id=\"smile\" class=\"smile-smile-_133_\">", $a);
        $a = str_replace(":sm134:", "<img id=\"smile\" class=\"smile-smile-_134_\">", $a);
        $a = str_replace(":sm135:", "<img id=\"smile\" class=\"smile-smile-_135_\">", $a);
        $a = str_replace(":sm136:", "<img id=\"smile\" class=\"smile-smile-_136_\">", $a);
        $a = str_replace(":sm137:", "<img id=\"smile\" class=\"smile-smile-_137_\">", $a);
        $a = str_replace(":sm138:", "<img id=\"smile\" class=\"smile-smile-_138_\">", $a);
        $a = str_replace(":sm139:", "<img id=\"smile\" class=\"smile-smile-_139_\">", $a);
        $a = str_replace(":sm140:", "<img id=\"smile\" class=\"smile-smile-_140_\">", $a);
        $a = str_replace(":sm141:", "<img id=\"smile\" class=\"smile-smile-_141_\">", $a);
        $a = str_replace(":sm142:", "<img id=\"smile\" class=\"smile-smile-_142_\">", $a);
        $a = str_replace(":sm143:", "<img id=\"smile\" class=\"smile-smile-_143_\">", $a);
        $a = str_replace(":sm144:", "<img id=\"smile\" class=\"smile-smile-_144_\">", $a);
        $a = str_replace(":sm145:", "<img id=\"smile\" class=\"smile-smile-_145_\">", $a);
        $a = str_replace(":sm146:", "<img id=\"smile\" class=\"smile-smile-_146_\">", $a);
        $a = str_replace(":sm147:", "<img id=\"smile\" class=\"smile-smile-_147_\">", $a);
        $a = str_replace(":sm148:", "<img id=\"smile\" class=\"smile-smile-_148_\">", $a);
        $a = str_replace(":sm149:", "<img id=\"smile\" class=\"smile-smile-_149_\">", $a);
        $a = str_replace(":sm150:", "<img id=\"smile\" class=\"smile-smile-_150_\">", $a);
        $a = str_replace(":sm151:", "<img id=\"smile\" class=\"smile-smile-_151_\">", $a);
        $a = str_replace(":sm152:", "<img id=\"smile\" class=\"smile-smile-_152_\">", $a);
        $a = str_replace(":sm153:", "<img id=\"smile\" class=\"smile-smile-_153_\">", $a);
        $a = str_replace(":sm154:", "<img id=\"smile\" class=\"smile-smile-_154_\">", $a);
        $a = str_replace(":sm155:", "<img id=\"smile\" class=\"smile-smile-_155_\">", $a);
        $a = str_replace(":sm156:", "<img id=\"smile\" class=\"smile-smile-_156_\">", $a);
        $a = str_replace(":sm157:", "<img id=\"smile\" class=\"smile-smile-_157_\">", $a);
        $a = str_replace(":sm158:", "<img id=\"smile\" class=\"smile-smile-_158_\">", $a);
        $a = str_replace(":sm159:", "<img id=\"smile\" class=\"smile-smile-_159_\">", $a);
        $a = str_replace(":sm160:", "<img id=\"smile\" class=\"smile-smile-_160_\">", $a);
        $a = str_replace(":sm161:", "<img id=\"smile\" class=\"smile-smile-_161_\">", $a);
        $a = str_replace(":sm162:", "<img id=\"smile\" class=\"smile-smile-_162_\">", $a);
        $a = str_replace(":sm163:", "<img id=\"smile\" class=\"smile-smile-_163_\">", $a);
        $a = str_replace(":sm164:", "<img id=\"smile\" class=\"smile-smile-_164_\">", $a);
        $a = str_replace(":sm165:", "<img id=\"smile\" class=\"smile-smile-_165_\">", $a);
        $a = str_replace(":sm166:", "<img id=\"smile\" class=\"smile-smile-_166_\">", $a);
        $a = str_replace(":sm167:", "<img id=\"smile\" class=\"smile-smile-_167_\">", $a);
        $a = str_replace(":sm168:", "<img id=\"smile\" class=\"smile-smile-_168_\">", $a);
        $a = str_replace(":sm169:", "<img id=\"smile\" class=\"smile-smile-_169_\">", $a);
        $a = str_replace(":sm170:", "<img id=\"smile\" class=\"smile-smile-_170_\">", $a);
        $a = str_replace(":sm171:", "<img id=\"smile\" class=\"smile-smile-_171_\">", $a);
        $a = str_replace(":sm172:", "<img id=\"smile\" class=\"smile-smile-_172_\">", $a);
        $a = str_replace(":sm173:", "<img id=\"smile\" class=\"smile-smile-_173_\">", $a);
        $a = str_replace(":sm174:", "<img id=\"smile\" class=\"smile-smile-_174_\">", $a);
        $a = str_replace(":sm175:", "<img id=\"smile\" class=\"smile-smile-_175_\">", $a);
        $a = str_replace(":sm176:", "<img id=\"smile\" class=\"smile-smile-_176_\">", $a);
        $a = str_replace(":sm177:", "<img id=\"smile\" class=\"smile-smile-_177_\">", $a);
        $a = str_replace(":sm178:", "<img id=\"smile\" class=\"smile-smile-_178_\">", $a);
        $a = str_replace(":sm179:", "<img id=\"smile\" class=\"smile-smile-_179_\">", $a);
        $a = str_replace(":sm180:", "<img id=\"smile\" class=\"smile-smile-_180_\">", $a);
        $a = str_replace(":sm181:", "<img id=\"smile\" class=\"smile-smile-_181_\">", $a);
        $a = str_replace(":sm182:", "<img id=\"smile\" class=\"smile-smile-_182_\">", $a);
        $a = str_replace(":sm183:", "<img id=\"smile\" class=\"smile-smile-_183_\">", $a);
        $a = str_replace(":sm184:", "<img id=\"smile\" class=\"smile-smile-_184_\">", $a);
        $a = str_replace(":sm185:", "<img id=\"smile\" class=\"smile-smile-_185_\">", $a);
        $a = str_replace(":sm186:", "<img id=\"smile\" class=\"smile-smile-_186_\">", $a);
        $a = str_replace(":sm187:", "<img id=\"smile\" class=\"smile-smile-_187_\">", $a);
        $a = str_replace(":sm188:", "<img id=\"smile\" class=\"smile-smile-_188_\">", $a);
        $a = str_replace(":sm189:", "<img id=\"smile\" class=\"smile-smile-_189_\">", $a);
        $a = str_replace(":sm190:", "<img id=\"smile\" class=\"smile-smile-_190_\">", $a);
        $a = str_replace(":sm191:", "<img id=\"smile\" class=\"smile-smile-_191_\">", $a);
        $a = str_replace(":sm192:", "<img id=\"smile\" class=\"smile-smile-_192_\">", $a);
        $a = str_replace(":sm193:", "<img id=\"smile\" class=\"smile-smile-_193_\">", $a);
        $a = str_replace(":sm194:", "<img id=\"smile\" class=\"smile-smile-_194_\">", $a);
        $a = str_replace(":sm195:", "<img id=\"smile\" class=\"smile-smile-_195_\">", $a);
        $a = str_replace(":sm196:", "<img id=\"smile\" class=\"smile-smile-_196_\">", $a);
        $a = str_replace(":sm197:", "<img id=\"smile\" class=\"smile-smile-_197_\">", $a);
        $a = str_replace(":sm198:", "<img id=\"smile\" class=\"smile-smile-_198_\">", $a);
        $a = str_replace(":sm199:", "<img id=\"smile\" class=\"smile-smile-_199_\">", $a);
        $a = str_replace(":sm200:", "<img id=\"smile\" class=\"smile-smile-_200_\">", $a);
        $a = str_replace(":sm201:", "<img id=\"smile\" class=\"smile-smile-_201_\">", $a);
        $a = str_replace(":sm202:", "<img id=\"smile\" class=\"smile-smile-_202_\">", $a);
        $a = str_replace(":sm203:", "<img id=\"smile\" class=\"smile-smile-_203_\">", $a);
        $a = str_replace(":sm204:", "<img id=\"smile\" class=\"smile-smile-_204_\">", $a);
        $a = str_replace(":sm205:", "<img id=\"smile\" class=\"smile-smile-_205_\">", $a);
        $a = str_replace(":sm206:", "<img id=\"smile\" class=\"smile-smile-_206_\">", $a);
        $a = str_replace(":sm207:", "<img id=\"smile\" class=\"smile-smile-_207_\">", $a);
        $a = str_replace(":sm208:", "<img id=\"smile\" class=\"smile-smile-_208_\">", $a);
        $a = str_replace(":sm209:", "<img id=\"smile\" class=\"smile-smile-_209_\">", $a);
        $a = str_replace(":sm210:", "<img id=\"smile\" class=\"smile-smile-_210_\">", $a);
        $a = str_replace(":sm211:", "<img id=\"smile\" class=\"smile-smile-_211_\">", $a);
        $a = str_replace(":sm212:", "<img id=\"smile\" class=\"smile-smile-_212_\">", $a);
        $a = str_replace(":sm213:", "<img id=\"smile\" class=\"smile-smile-_213_\">", $a);
        $a = str_replace(":sm214:", "<img id=\"smile\" class=\"smile-smile-_214_\">", $a);
        $a = str_replace(":sm215:", "<img id=\"smile\" class=\"smile-smile-_215_\">", $a);
        $a = str_replace(":sm216:", "<img id=\"smile\" class=\"smile-smile-_216_\">", $a);
        $a = str_replace(":sm217:", "<img id=\"smile\" class=\"smile-smile-_217_\">", $a);
        $a = str_replace(":sm218:", "<img id=\"smile\" class=\"smile-smile-_218_\">", $a);
        $a = str_replace(":sm219:", "<img id=\"smile\" class=\"smile-smile-_219_\">", $a);
        $a = str_replace(":sm220:", "<img id=\"smile\" class=\"smile-smile-_220_\">", $a);
        $a = str_replace(":sm221:", "<img id=\"smile\" class=\"smile-smile-_221_\">", $a);
        $a = str_replace(":sm222:", "<img id=\"smile\" class=\"smile-smile-_222_\">", $a);
        $a = str_replace(":sm223:", "<img id=\"smile\" class=\"smile-smile-_223_\">", $a);
        $a = str_replace(":sm224:", "<img id=\"smile\" class=\"smile-smile-_224_\">", $a);
        $a = str_replace(":sm225:", "<img id=\"smile\" class=\"smile-smile-_225_\">", $a);
        $a = str_replace(":sm226:", "<img id=\"smile\" class=\"smile-smile-_226_\">", $a);
        $a = str_replace(":sm227:", "<img id=\"smile\" class=\"smile-smile-_227_\">", $a);
        $a = str_replace(":sm228:", "<img id=\"smile\" class=\"smile-smile-_228_\">", $a);
        $a = str_replace(":sm229:", "<img id=\"smile\" class=\"smile-smile-_229_\">", $a);
        $a = str_replace(":sm230:", "<img id=\"smile\" class=\"smile-smile-_230_\">", $a);
        $a = str_replace(":sm231:", "<img id=\"smile\" class=\"smile-smile-_231_\">", $a);
        $a = str_replace(":sm232:", "<img id=\"smile\" class=\"smile-smile-_232_\">", $a);
        $a = str_replace(":sm233:", "<img id=\"smile\" class=\"smile-smile-_233_\">", $a);
        $a = str_replace(":sm234:", "<img id=\"smile\" class=\"smile-smile-_234_\">", $a);
        $a = str_replace(":sm235:", "<img id=\"smile\" class=\"smile-smile-_235_\">", $a);
        $a = str_replace(":sm236:", "<img id=\"smile\" class=\"smile-smile-_236_\">", $a);
        $a = str_replace(":sm237:", "<img id=\"smile\" class=\"smile-smile-_237_\">", $a);
        $a = str_replace(":sm238:", "<img id=\"smile\" class=\"smile-smile-_238_\">", $a);
        $a = str_replace(":sm239:", "<img id=\"smile\" class=\"smile-smile-_239_\">", $a);
        $a = str_replace(":sm240:", "<img id=\"smile\" class=\"smile-smile-_240_\">", $a);
        $a = str_replace(":sm241:", "<img id=\"smile\" class=\"smile-smile-_241_\">", $a);
        $a = str_replace(":sm242:", "<img id=\"smile\" class=\"smile-smile-_242_\">", $a);
        $a = str_replace(":sm243:", "<img id=\"smile\" class=\"smile-smile-_243_\">", $a);
        $a = str_replace(":sm244:", "<img id=\"smile\" class=\"smile-smile-_244_\">", $a);
        $a = str_replace(":sm245:", "<img id=\"smile\" class=\"smile-smile-_245_\">", $a);
        $a = str_replace(":sm246:", "<img id=\"smile\" class=\"smile-smile-_246_\">", $a);
        $a = str_replace(":sm247:", "<img id=\"smile\" class=\"smile-smile-_247_\">", $a);
        $a = str_replace(":sm248:", "<img id=\"smile\" class=\"smile-smile-_248_\">", $a);
        $a = str_replace(":sm249:", "<img id=\"smile\" class=\"smile-smile-_249_\">", $a);
        $a = str_replace(":sm250:", "<img id=\"smile\" class=\"smile-smile-_250_\">", $a);
        $a = str_replace(":sm251:", "<img id=\"smile\" class=\"smile-smile-_251_\">", $a);
        $a = str_replace(":sm252:", "<img id=\"smile\" class=\"smile-smile-_252_\">", $a);
        $a = str_replace(":sm253:", "<img id=\"smile\" class=\"smile-smile-_253_\">", $a);
        $a = str_replace(":sm254:", "<img id=\"smile\" class=\"smile-smile-_254_\">", $a);
        $a = str_replace(":sm255:", "<img id=\"smile\" class=\"smile-smile-_255_\">", $a);
        $a = str_replace(":sm256:", "<img id=\"smile\" class=\"smile-smile-_256_\">", $a);
        $a = str_replace(":sm257:", "<img id=\"smile\" class=\"smile-smile-_257_\">", $a);
        $a = str_replace(":sm258:", "<img id=\"smile\" class=\"smile-smile-_258_\">", $a);
        $a = str_replace(":sm259:", "<img id=\"smile\" class=\"smile-smile-_259_\">", $a);
        $a = str_replace(":sm260:", "<img id=\"smile\" class=\"smile-smile-_260_\">", $a);
        $a = str_replace(":sm261:", "<img id=\"smile\" class=\"smile-smile-_261_\">", $a);
        $a = str_replace(":sm262:", "<img id=\"smile\" class=\"smile-smile-_262_\">", $a);
        $a = str_replace(":sm263:", "<img id=\"smile\" class=\"smile-smile-_263_\">", $a);
        $a = str_replace(":sm264:", "<img id=\"smile\" class=\"smile-smile-_264_\">", $a);
        $a = str_replace(":sm265:", "<img id=\"smile\" class=\"smile-smile-_265_\">", $a);
        $a = str_replace(":sm266:", "<img id=\"smile\" class=\"smile-smile-_266_\">", $a);
        $a = str_replace(":sm267:", "<img id=\"smile\" class=\"smile-smile-_267_\">", $a);
        $a = str_replace(":sm268:", "<img id=\"smile\" class=\"smile-smile-_268_\">", $a);
        $a = str_replace(":sm269:", "<img id=\"smile\" class=\"smile-smile-_269_\">", $a);
        $a = str_replace(":sm270:", "<img id=\"smile\" class=\"smile-smile-_270_\">", $a);
        $a = str_replace(":sm271:", "<img id=\"smile\" class=\"smile-smile-_271_\">", $a);
        $a = str_replace(":sm272:", "<img id=\"smile\" class=\"smile-smile-_272_\">", $a);
        $a = str_replace(":sm273:", "<img id=\"smile\" class=\"smile-smile-_273_\">", $a);
        $a = str_replace(":sm274:", "<img id=\"smile\" class=\"smile-smile-_274_\">", $a);
        $a = str_replace(":sm275:", "<img id=\"smile\" class=\"smile-smile-_275_\">", $a);
        $a = str_replace(":sm276:", "<img id=\"smile\" class=\"smile-smile-_276_\">", $a);
        $a = str_replace(":sm277:", "<img id=\"smile\" class=\"smile-smile-_277_\">", $a);
        $a = str_replace(":sm278:", "<img id=\"smile\" class=\"smile-smile-_278_\">", $a);
        $a = str_replace(":sm279:", "<img id=\"smile\" class=\"smile-smile-_279_\">", $a);
        $a = str_replace(":sm280:", "<img id=\"smile\" class=\"smile-smile-_280_\">", $a);
        $a = str_replace(":sm281:", "<img id=\"smile\" class=\"smile-smile-_281_\">", $a);
        $a = str_replace(":sm282:", "<img id=\"smile\" class=\"smile-smile-_282_\">", $a);
        $a = str_replace(":sm283:", "<img id=\"smile\" class=\"smile-smile-_283_\">", $a);
        $a = str_replace(":sm284:", "<img id=\"smile\" class=\"smile-smile-_284_\">", $a);
        $a = str_replace(":sm285:", "<img id=\"smile\" class=\"smile-smile-_285_\">", $a);
        $a = str_replace(":sm286:", "<img id=\"smile\" class=\"smile-smile-_286_\">", $a);
        $a = str_replace(":sm287:", "<img id=\"smile\" class=\"smile-smile-_287_\">", $a);
        $a = str_replace(":sm288:", "<img id=\"smile\" class=\"smile-smile-_288_\">", $a);
        $a = str_replace(":sm289:", "<img id=\"smile\" class=\"smile-smile-_289_\">", $a);
        $a = str_replace(":sm290:", "<img id=\"smile\" class=\"smile-smile-_290_\">", $a);
        $a = str_replace(":sm291:", "<img id=\"smile\" class=\"smile-smile-_291_\">", $a);
        $a = str_replace(":sm292:", "<img id=\"smile\" class=\"smile-smile-_292_\">", $a);
        $a = str_replace(":sm293:", "<img id=\"smile\" class=\"smile-smile-_293_\">", $a);
        $a = str_replace(":sm294:", "<img id=\"smile\" class=\"smile-smile-_294_\">", $a);
        $a = str_replace(":sm295:", "<img id=\"smile\" class=\"smile-smile-_295_\">", $a);
        $a = str_replace(":sm296:", "<img id=\"smile\" class=\"smile-smile-_296_\">", $a);
        $a = str_replace(":sm297:", "<img id=\"smile\" class=\"smile-smile-_297_\">", $a);
        $a = str_replace(":sm298:", "<img id=\"smile\" class=\"smile-smile-_298_\">", $a);
        $a = str_replace(":sm299:", "<img id=\"smile\" class=\"smile-smile-_299_\">", $a);
        $a = str_replace(":sm300:", "<img id=\"smile\" class=\"smile-smile-_300_\">", $a);
        $a = str_replace(":sm301:", "<img id=\"smile\" class=\"smile-smile-_301_\">", $a);
        $a = str_replace(":sm302:", "<img id=\"smile\" class=\"smile-smile-_302_\">", $a);
        $a = str_replace(":sm303:", "<img id=\"smile\" class=\"smile-smile-_303_\">", $a);
        $a = str_replace(":sm304:", "<img id=\"smile\" class=\"smile-smile-_304_\">", $a);
        $a = str_replace(":sm305:", "<img id=\"smile\" class=\"smile-smile-_305_\">", $a);
        $a = str_replace(":sm306:", "<img id=\"smile\" class=\"smile-smile-_306_\">", $a);
        $a = str_replace(":sm307:", "<img id=\"smile\" class=\"smile-smile-_307_\">", $a);
        $a = str_replace(":sm308:", "<img id=\"smile\" class=\"smile-smile-_308_\">", $a);
        $a = str_replace(":sm309:", "<img id=\"smile\" class=\"smile-smile-_309_\">", $a);
        $a = str_replace(":sm310:", "<img id=\"smile\" class=\"smile-smile-_310_\">", $a);
        $a = str_replace(":sm311:", "<img id=\"smile\" class=\"smile-smile-_311_\">", $a);
        $a = str_replace(":sm312:", "<img id=\"smile\" class=\"smile-smile-_312_\">", $a);
        $a = str_replace(":sm313:", "<img id=\"smile\" class=\"smile-smile-_313_\">", $a);
        $a = str_replace(":sm314:", "<img id=\"smile\" class=\"smile-smile-_314_\">", $a);
        $a = str_replace(":sm315:", "<img id=\"smile\" class=\"smile-smile-_315_\">", $a);
        $a = str_replace(":sm316:", "<img id=\"smile\" class=\"smile-smile-_316_\">", $a);
        $a = str_replace(":sm317:", "<img id=\"smile\" class=\"smile-smile-_317_\">", $a);
        $a = str_replace(":sm318:", "<img id=\"smile\" class=\"smile-smile-_318_\">", $a);
        $a = str_replace(":sm319:", "<img id=\"smile\" class=\"smile-smile-_319_\">", $a);
        $a = str_replace(":sm320:", "<img id=\"smile\" class=\"smile-smile-_320_\">", $a);
        $a = str_replace(":sm321:", "<img id=\"smile\" class=\"smile-smile-_321_\">", $a);
        $a = str_replace(":sm322:", "<img id=\"smile\" class=\"smile-smile-_322_\">", $a);
        $a = str_replace(":sm323:", "<img id=\"smile\" class=\"smile-smile-_323_\">", $a);
        $a = str_replace(":sm324:", "<img id=\"smile\" class=\"smile-smile-_324_\">", $a);
        $a = str_replace(":sm325:", "<img id=\"smile\" class=\"smile-smile-_325_\">", $a);
        $a = str_replace(":sm326:", "<img id=\"smile\" class=\"smile-smile-_326_\">", $a);
        $a = str_replace(":sm327:", "<img id=\"smile\" class=\"smile-smile-_327_\">", $a);
        $a = str_replace(":sm328:", "<img id=\"smile\" class=\"smile-smile-_328_\">", $a);
        $a = str_replace(":sm329:", "<img id=\"smile\" class=\"smile-smile-_329_\">", $a);
        $a = str_replace(":sm330:", "<img id=\"smile\" class=\"smile-smile-_330_\">", $a);
        $a = str_replace(":sm331:", "<img id=\"smile\" class=\"smile-smile-_331_\">", $a);
        $a = str_replace(":sm332:", "<img id=\"smile\" class=\"smile-smile-_332_\">", $a);
        $a = str_replace(":sm333:", "<img id=\"smile\" class=\"smile-smile-_333_\">", $a);
        $a = str_replace(":sm334:", "<img id=\"smile\" class=\"smile-smile-_334_\">", $a);
        $a = str_replace(":sm335:", "<img id=\"smile\" class=\"smile-smile-_335_\">", $a);
        $a = str_replace(":sm336:", "<img id=\"smile\" class=\"smile-smile-_336_\">", $a);
        $a = str_replace(":sm337:", "<img id=\"smile\" class=\"smile-smile-_337_\">", $a);
        $a = str_replace(":sm338:", "<img id=\"smile\" class=\"smile-smile-_338_\">", $a);
        $a = str_replace(":sm339:", "<img id=\"smile\" class=\"smile-smile-_339_\">", $a);
        $a = str_replace(":sm340:", "<img id=\"smile\" class=\"smile-smile-_340_\">", $a);
        $a = str_replace(":sm341:", "<img id=\"smile\" class=\"smile-smile-_341_\">", $a);
        $a = str_replace(":sm342:", "<img id=\"smile\" class=\"smile-smile-_342_\">", $a);
        $a = str_replace(":sm343:", "<img id=\"smile\" class=\"smile-smile-_343_\">", $a);
        $a = str_replace(":sm344:", "<img id=\"smile\" class=\"smile-smile-_344_\">", $a);
        $a = str_replace(":sm345:", "<img id=\"smile\" class=\"smile-smile-_345_\">", $a);
        $a = str_replace(":sm346:", "<img id=\"smile\" class=\"smile-smile-_346_\">", $a);
        $a = str_replace(":sm347:", "<img id=\"smile\" class=\"smile-smile-_347_\">", $a);
        $a = str_replace(":sm348:", "<img id=\"smile\" class=\"smile-smile-_348_\">", $a);
        $a = str_replace(":sm349:", "<img id=\"smile\" class=\"smile-smile-_349_\">", $a);
        $a = str_replace(":sm350:", "<img id=\"smile\" class=\"smile-smile-_350_\">", $a);
        $a = str_replace(":sm351:", "<img id=\"smile\" class=\"smile-smile-_351_\">", $a);
        $a = str_replace(":sm352:", "<img id=\"smile\" class=\"smile-smile-_352_\">", $a);
        $a = str_replace(":sm353:", "<img id=\"smile\" class=\"smile-smile-_353_\">", $a);
        $a = str_replace(":sm354:", "<img id=\"smile\" class=\"smile-smile-_354_\">", $a);
        $a = str_replace(":sm355:", "<img id=\"smile\" class=\"smile-smile-_355_\">", $a);
        $a = str_replace(":sm356:", "<img id=\"smile\" class=\"smile-smile-_356_\">", $a);
        $a = str_replace(":sm357:", "<img id=\"smile\" class=\"smile-smile-_357_\">", $a);
        $a = str_replace(":sm358:", "<img id=\"smile\" class=\"smile-smile-_358_\">", $a);
        $a = str_replace(":sm359:", "<img id=\"smile\" class=\"smile-smile-_359_\">", $a);
        $a = str_replace(":sm360:", "<img id=\"smile\" class=\"smile-smile-_360_\">", $a);
        $a = str_replace(":sm361:", "<img id=\"smile\" class=\"smile-smile-_361_\">", $a);
        $a = str_replace(":sm362:", "<img id=\"smile\" class=\"smile-smile-_362_\">", $a);
        $a = str_replace(":sm363:", "<img id=\"smile\" class=\"smile-smile-_363_\">", $a);
        $a = str_replace(":sm364:", "<img id=\"smile\" class=\"smile-smile-_364_\">", $a);
        $a = str_replace(":sm365:", "<img id=\"smile\" class=\"smile-smile-_365_\">", $a);
        $a = str_replace(":sm366:", "<img id=\"smile\" class=\"smile-smile-_366_\">", $a);
        $a = str_replace(":sm367:", "<img id=\"smile\" class=\"smile-smile-_367_\">", $a);
        $a = str_replace(":sm368:", "<img id=\"smile\" class=\"smile-smile-_368_\">", $a);
        $a = str_replace(":sm369:", "<img id=\"smile\" class=\"smile-smile-_369_\">", $a);
        $a = str_replace(":sm370:", "<img id=\"smile\" class=\"smile-smile-_370_\">", $a);
        $a = str_replace(":sm371:", "<img id=\"smile\" class=\"smile-smile-_371_\">", $a);
        $a = str_replace(":sm372:", "<img id=\"smile\" class=\"smile-smile-_372_\">", $a);
        $a = str_replace(":sm373:", "<img id=\"smile\" class=\"smile-smile-_373_\">", $a);
        $a = str_replace(":sm374:", "<img id=\"smile\" class=\"smile-smile-_374_\">", $a);
        $a = str_replace(":sm375:", "<img id=\"smile\" class=\"smile-smile-_375_\">", $a);
        $a = str_replace(":sm376:", "<img id=\"smile\" class=\"smile-smile-_376_\">", $a);
        $a = str_replace(":sm377:", "<img id=\"smile\" class=\"smile-smile-_377_\">", $a);
        $a = str_replace(":sm378:", "<img id=\"smile\" class=\"smile-smile-_378_\">", $a);
        $a = str_replace(":sm379:", "<img id=\"smile\" class=\"smile-smile-_379_\">", $a);
        $a = str_replace(":sm380:", "<img id=\"smile\" class=\"smile-smile-_380_\">", $a);
        $a = str_replace(":sm381:", "<img id=\"smile\" class=\"smile-smile-_381_\">", $a);
        $a = str_replace(":sm382:", "<img id=\"smile\" class=\"smile-smile-_382_\">", $a);
        $a = str_replace(":sm383:", "<img id=\"smile\" class=\"smile-smile-_383_\">", $a);
        $a = str_replace(":sm384:", "<img id=\"smile\" class=\"smile-smile-_384_\">", $a);
        $a = str_replace(":sm385:", "<img id=\"smile\" class=\"smile-smile-_385_\">", $a);
        $a = str_replace(":sm386:", "<img id=\"smile\" class=\"smile-smile-_386_\">", $a);
        $a = str_replace(":sm387:", "<img id=\"smile\" class=\"smile-smile-_387_\">", $a);
        $a = str_replace(":sm388:", "<img id=\"smile\" class=\"smile-smile-_388_\">", $a);
        $a = str_replace(":sm389:", "<img id=\"smile\" class=\"smile-smile-_389_\">", $a);
        $a = str_replace(":sm390:", "<img id=\"smile\" class=\"smile-smile-_390_\">", $a);
        $a = str_replace(":sm391:", "<img id=\"smile\" class=\"smile-smile-_391_\">", $a);
        $a = str_replace(":sm392:", "<img id=\"smile\" class=\"smile-smile-_392_\">", $a);
        $a = str_replace(":sm393:", "<img id=\"smile\" class=\"smile-smile-_393_\">", $a);
        $a = str_replace(":sm394:", "<img id=\"smile\" class=\"smile-smile-_394_\">", $a);
        $a = str_replace(":sm395:", "<img id=\"smile\" class=\"smile-smile-_395_\">", $a);
        $a = str_replace(":sm396:", "<img id=\"smile\" class=\"smile-smile-_396_\">", $a);
        $a = str_replace(":sm397:", "<img id=\"smile\" class=\"smile-smile-_397_\">", $a);
        $a = str_replace(":sm398:", "<img id=\"smile\" class=\"smile-smile-_398_\">", $a);
        $a = str_replace(":sm399:", "<img id=\"smile\" class=\"smile-smile-_399_\">", $a);
        $a = str_replace(":sm400:", "<img id=\"smile\" class=\"smile-smile-_400_\">", $a);
        $a = str_replace(":sm401:", "<img id=\"smile\" class=\"smile-smile-_401_\">", $a);
        $a = str_replace(":sm402:", "<img id=\"smile\" class=\"smile-smile-_402_\">", $a);
        $a = str_replace(":sm403:", "<img id=\"smile\" class=\"smile-smile-_403_\">", $a);
        $a = str_replace(":sm404:", "<img id=\"smile\" class=\"smile-smile-_404_\">", $a);
        $a = str_replace(":sm405:", "<img id=\"smile\" class=\"smile-smile-_405_\">", $a);
        $a = str_replace(":sm406:", "<img id=\"smile\" class=\"smile-smile-_406_\">", $a);
        $a = str_replace(":sm407:", "<img id=\"smile\" class=\"smile-smile-_407_\">", $a);
        $a = str_replace(":sm408:", "<img id=\"smile\" class=\"smile-smile-_408_\">", $a);
        $a = str_replace(":sm409:", "<img id=\"smile\" class=\"smile-smile-_409_\">", $a);
        $a = str_replace(":sm410:", "<img id=\"smile\" class=\"smile-smile-_410_\">", $a);
        $a = str_replace(":sm411:", "<img id=\"smile\" class=\"smile-smile-_411_\">", $a);
        $a = str_replace(":sm412:", "<img id=\"smile\" class=\"smile-smile-_412_\">", $a);
        $a = str_replace(":sm413:", "<img id=\"smile\" class=\"smile-smile-_413_\">", $a);
        $a = str_replace(":sm414:", "<img id=\"smile\" class=\"smile-smile-_414_\">", $a);
        $a = str_replace(":sm415:", "<img id=\"smile\" class=\"smile-smile-_415_\">", $a);
        $a = str_replace(":sm416:", "<img id=\"smile\" class=\"smile-smile-_416_\">", $a);
        $a = str_replace(":sm417:", "<img id=\"smile\" class=\"smile-smile-_417_\">", $a);
        $a = str_replace(":sm418:", "<img id=\"smile\" class=\"smile-smile-_418_\">", $a);
        $a = str_replace(":sm419:", "<img id=\"smile\" class=\"smile-smile-_419_\">", $a);
        $a = str_replace(":sm420:", "<img id=\"smile\" class=\"smile-smile-_420_\">", $a);
        $a = str_replace(":sm421:", "<img id=\"smile\" class=\"smile-smile-_421_\">", $a);
        $a = str_replace(":sm422:", "<img id=\"smile\" class=\"smile-smile-_422_\">", $a);
        $a = str_replace(":sm423:", "<img id=\"smile\" class=\"smile-smile-_423_\">", $a);
        $a = str_replace(":sm424:", "<img id=\"smile\" class=\"smile-smile-_424_\">", $a);
        $a = str_replace(":sm425:", "<img id=\"smile\" class=\"smile-smile-_425_\">", $a);
        $a = str_replace(":sm426:", "<img id=\"smile\" class=\"smile-smile-_426_\">", $a);
        $a = str_replace(":sm427:", "<img id=\"smile\" class=\"smile-smile-_427_\">", $a);
        $a = str_replace(":sm428:", "<img id=\"smile\" class=\"smile-smile-_428_\">", $a);
        $a = str_replace(":sm429:", "<img id=\"smile\" class=\"smile-smile-_429_\">", $a);
        $a = str_replace(":sm430:", "<img id=\"smile\" class=\"smile-smile-_430_\">", $a);
        $a = str_replace(":sm431:", "<img id=\"smile\" class=\"smile-smile-_431_\">", $a);
        $a = str_replace(":sm432:", "<img id=\"smile\" class=\"smile-smile-_432_\">", $a);
        $a = str_replace(":sm433:", "<img id=\"smile\" class=\"smile-smile-_433_\">", $a);
        $a = str_replace(":sm434:", "<img id=\"smile\" class=\"smile-smile-_434_\">", $a);
        $a = str_replace(":sm435:", "<img id=\"smile\" class=\"smile-smile-_435_\">", $a);
        $a = str_replace(":sm436:", "<img id=\"smile\" class=\"smile-smile-_436_\">", $a);
        $a = str_replace(":sm437:", "<img id=\"smile\" class=\"smile-smile-_437_\">", $a);
        $a = str_replace(":sm438:", "<img id=\"smile\" class=\"smile-smile-_438_\">", $a);
        $a = str_replace(":sm439:", "<img id=\"smile\" class=\"smile-smile-_439_\">", $a);
        $a = str_replace(":sm440:", "<img id=\"smile\" class=\"smile-smile-_440_\">", $a);
        $a = str_replace(":sm441:", "<img id=\"smile\" class=\"smile-smile-_441_\">", $a);
        $a = str_replace(":sm442:", "<img id=\"smile\" class=\"smile-smile-_442_\">", $a);
        $a = str_replace(":sm443:", "<img id=\"smile\" class=\"smile-smile-_443_\">", $a);
        $a = str_replace(":sm444:", "<img id=\"smile\" class=\"smile-smile-_444_\">", $a);
        $a = str_replace(":sm445:", "<img id=\"smile\" class=\"smile-smile-_445_\">", $a);
        $a = str_replace(":sm446:", "<img id=\"smile\" class=\"smile-smile-_446_\">", $a);
        $a = str_replace(":sm447:", "<img id=\"smile\" class=\"smile-smile-_447_\">", $a);
        $a = str_replace(":sm448:", "<img id=\"smile\" class=\"smile-smile-_448_\">", $a);
        $a = str_replace(":sm449:", "<img id=\"smile\" class=\"smile-smile-_449_\">", $a);
        $a = str_replace(":sm450:", "<img id=\"smile\" class=\"smile-smile-_450_\">", $a);
        $a = str_replace(":sm451:", "<img id=\"smile\" class=\"smile-smile-_451_\">", $a);
        $a = str_replace(":sm452:", "<img id=\"smile\" class=\"smile-smile-_452_\">", $a);
        $a = str_replace(":sm453:", "<img id=\"smile\" class=\"smile-smile-_453_\">", $a);
        $a = str_replace(":sm454:", "<img id=\"smile\" class=\"smile-smile-_454_\">", $a);
        $a = str_replace(":sm455:", "<img id=\"smile\" class=\"smile-smile-_455_\">", $a);
        $a = str_replace(":sm456:", "<img id=\"smile\" class=\"smile-smile-_456_\">", $a);
        $a = str_replace(":sm457:", "<img id=\"smile\" class=\"smile-smile-_457_\">", $a);
        $a = str_replace(":sm458:", "<img id=\"smile\" class=\"smile-smile-_458_\">", $a);
        $a = str_replace(":sm459:", "<img id=\"smile\" class=\"smile-smile-_459_\">", $a);
        $a = str_replace(":sm460:", "<img id=\"smile\" class=\"smile-smile-_460_\">", $a);
        $a = str_replace(":sm461:", "<img id=\"smile\" class=\"smile-smile-_461_\">", $a);
        $a = str_replace(":sm462:", "<img id=\"smile\" class=\"smile-smile-_462_\">", $a);
        $a = str_replace(":sm463:", "<img id=\"smile\" class=\"smile-smile-_463_\">", $a);
        $a = str_replace(":sm464:", "<img id=\"smile\" class=\"smile-smile-_464_\">", $a);
        $a = str_replace(":sm465:", "<img id=\"smile\" class=\"smile-smile-_465_\">", $a);
        $a = str_replace(":sm466:", "<img id=\"smile\" class=\"smile-smile-_466_\">", $a);
        $a = str_replace(":sm467:", "<img id=\"smile\" class=\"smile-smile-_467_\">", $a);
        $a = str_replace(":sm468:", "<img id=\"smile\" class=\"smile-smile-_468_\">", $a);
        $a = str_replace(":sm469:", "<img id=\"smile\" class=\"smile-smile-_469_\">", $a);
        $a = str_replace(":sm470:", "<img id=\"smile\" class=\"smile-smile-_470_\">", $a);
        $a = str_replace(":sm471:", "<img id=\"smile\" class=\"smile-smile-_471_\">", $a);
        $a = str_replace(":sm472:", "<img id=\"smile\" class=\"smile-smile-_472_\">", $a);
        $a = str_replace(":sm473:", "<img id=\"smile\" class=\"smile-smile-_473_\">", $a);
        $a = str_replace(":sm474:", "<img id=\"smile\" class=\"smile-smile-_474_\">", $a);
        $a = str_replace(":sm475:", "<img id=\"smile\" class=\"smile-smile-_475_\">", $a);
        $a = str_replace(":sm476:", "<img id=\"smile\" class=\"smile-smile-_476_\">", $a);
        $a = str_replace(":sm477:", "<img id=\"smile\" class=\"smile-smile-_477_\">", $a);
        $a = str_replace(":sm478:", "<img id=\"smile\" class=\"smile-smile-_478_\">", $a);
        $a = str_replace(":sm479:", "<img id=\"smile\" class=\"smile-smile-_479_\">", $a);
        $a = str_replace(":sm480:", "<img id=\"smile\" class=\"smile-smile-_480_\">", $a);
        $a = str_replace(":sm481:", "<img id=\"smile\" class=\"smile-smile-_481_\">", $a);
        $a = str_replace(":sm482:", "<img id=\"smile\" class=\"smile-smile-_482_\">", $a);
        $a = str_replace(":sm483:", "<img id=\"smile\" class=\"smile-smile-_483_\">", $a);
        $a = str_replace(":sm484:", "<img id=\"smile\" class=\"smile-smile-_484_\">", $a);
        $a = str_replace(":sm485:", "<img id=\"smile\" class=\"smile-smile-_485_\">", $a);
        $a = str_replace(":sm486:", "<img id=\"smile\" class=\"smile-smile-_486_\">", $a);
        $a = str_replace(":sm487:", "<img id=\"smile\" class=\"smile-smile-_487_\">", $a);
        $a = str_replace(":sm488:", "<img id=\"smile\" class=\"smile-smile-_488_\">", $a);
        $a = str_replace(":sm489:", "<img id=\"smile\" class=\"smile-smile-_489_\">", $a);
        $a = str_replace(":sm490:", "<img id=\"smile\" class=\"smile-smile-_490_\">", $a);
        $a = str_replace(":sm491:", "<img id=\"smile\" class=\"smile-smile-_491_\">", $a);
        $a = str_replace(":sm492:", "<img id=\"smile\" class=\"smile-smile-_492_\">", $a);
        $a = str_replace(":sm493:", "<img id=\"smile\" class=\"smile-smile-_493_\">", $a);
        $a = str_replace(":sm494:", "<img id=\"smile\" class=\"smile-smile-_494_\">", $a);
        $a = str_replace(":sm495:", "<img id=\"smile\" class=\"smile-smile-_495_\">", $a);
        $a = str_replace(":sm496:", "<img id=\"smile\" class=\"smile-smile-_496_\">", $a);
        $a = str_replace(":sm497:", "<img id=\"smile\" class=\"smile-smile-_497_\">", $a);
        $a = str_replace(":sm498:", "<img id=\"smile\" class=\"smile-smile-_498_\">", $a);
        $a = str_replace(":sm499:", "<img id=\"smile\" class=\"smile-smile-_499_\">", $a);
        $a = str_replace(":sm500:", "<img id=\"smile\" class=\"smile-smile-_500_\">", $a);
        $a = str_replace(":sm501:", "<img id=\"smile\" class=\"smile-smile-_501_\">", $a);
        $a = str_replace(":sm502:", "<img id=\"smile\" class=\"smile-smile-_502_\">", $a);
        $a = str_replace(":sm503:", "<img id=\"smile\" class=\"smile-smile-_503_\">", $a);
        $a = str_replace(":sm504:", "<img id=\"smile\" class=\"smile-smile-_504_\">", $a);
        $a = str_replace(":sm505:", "<img id=\"smile\" class=\"smile-smile-_505_\">", $a);
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
