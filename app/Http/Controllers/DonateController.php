<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Shop;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\GDonate\GDonate;
use App\Services\GDonate\GDonateEvent;
use App\CCentrifugo;
class DonateController extends Controller
{

    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }
    
    public function Donate(Request $request)
    {
        $payment = \DB::table('freekassa_payments')->where('id', $request->get('MERCHANT_ORDER_ID'))->first();
        if(is_null($payment)) return "Not Found";
        if($payment->status != 0) return "Already Payed";
        $sign = md5(config('pay.freekassa_id').':'.$request->get('AMOUNT').':'.config('pay.freekassa_s2').':'.$request->get('MERCHANT_ORDER_ID'));
        if ($request->get('SIGN') != $sign) return "Wrong SIGN";
        $user = User::find($payment->account); $vip = 1;
        if (config('pay.vip_only') && (strpos(strtolower(' '.$user->username),  strtolower(config('app.sitename'))) == false)) $vip = 0;
        $sum = $request->get('AMOUNT') + ($vip * $request->get('AMOUNT') * config('pay.factor')/100);
        User::mchange($user->id, $sum);
        User::slchange($user->id, $sum / 100 * config('mod_game.slimit'));
        $this->_responseMessageToSite('Пополнениее засчитано | Сумма: ' . $sum , $user->steamid64);
        \DB::table('deposits')->insertGetId([
            'user_id' => $user->id, 
            'date' => Carbon::now()->toDateTimeString(),
            'price' => $sum,
            'type' => Shop::D_MONEY
        ]);
        \DB::table('freekassa_payments')->where('id', $request->get('MERCHANT_ORDER_ID'))->update(['status' => 1, 'intid' => $request->get('intid'), 'P_EMAIL' => $request->get('P_EMAIL'), 'P_PHONE' => $request->get('P_PHONE'), 'dateComplete' => Carbon::now()->toDateTimeString(), 'AMOUNT' => $sum]);
        return "YES";
    }

}
