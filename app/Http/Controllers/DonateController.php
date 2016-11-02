<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\GDonate\GDonate;
use App\Services\GDonate\GDonateEvent;

class DonateController extends Controller
{

    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
	
    public function Donate(Request $request)
    {
		if(\App\Http\Controllers\GameController::PayType == 0){
			$payment = new GDonate(
				new GDonateEvent(),
				$request
			);
			return $payment->getResult();
		}
		if(\App\Http\Controllers\GameController::PayType == 1){
			$payment = \DB::table('freekassa_payments')->where('id', $request->get('MERCHANT_ORDER_ID'))->first();
			if(!is_null($payment)){
				if($payment->status == 0){
					$sign = md5(\App\Http\Controllers\GameController::FreeKassaID.':'.$request->get('AMOUNT').':'.\App\Http\Controllers\GameController::FreeKassaSecret2.':'.$request->get('MERCHANT_ORDER_ID'));
					if ($request->get('SIGN') == $sign){
						$user = User::find($payment->account);
						$user->money = $user->money + $request->get('AMOUNT') * 1.25;
						$user->save();
						$this->_responseMessageToSite('Пополнениее засчитано | Сумма: ' . $request->get('AMOUNT') , $user->steamid64);
						\DB::table('deposits')->insertGetId([
							'user_id' => $user->id, 
							'date' => Carbon::now()->toDateTimeString(),
							'price' => $request->get('AMOUNT'),
							'type' => 3
						]);
						\DB::table('freekassa_payments')->where('id', $request->get('MERCHANT_ORDER_ID'))->update(['status' => 1, 'intid' => $request->get('intid'), 'P_EMAIL' => $request->get('P_EMAIL'), 'P_PHONE' => $request->get('P_PHONE'), 'dateComplete' => Carbon::now()->toDateTimeString(), 'AMOUNT' => $request->get('AMOUNT')]);
						return "YES";
					} else return "Wrong SIGN";
				} else return "Already Payed";
			} else return "Not Found";
		}
    }

}
