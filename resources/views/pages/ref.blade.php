@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\RefController::TITLE_UP }}</title>
<link href="{{ $asset('assets/css/ref.css') }}" rel="stylesheet">

<div class="content">
	<div class="title-block">
		<h2>РЕФЕРАЛЬНАЯ СИСТЕМА</h2>
	</div>
	<div class="advert-banner" style="text-align:center;">
        <div style="display: inline-block;"><b>ВНИМАНИЕ!</b> ЕСЛИ У ВАС НЕТ РЕФЕРАЛЬНОГО КОДА - МОЖЕТЕ ВВЕСТИ НАШ: <b>SKONIKS</b></div>
    </div>
	<div class="buy-cards-container" style="padding-top: 10px;">
		<div class="buy-cards-block" style="margin-top: 0px; text-align:center;">
			<div style="float: center; display: inline-block">
				<div class="buy-card-item">
					<span class="text">За активацию купона вы получаете</span><br><span class="text">Валютой на сайте</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">{{\App\Http\Controllers\RefController::ADD_MONEY_MAIN_REF }}</span><span class="text">руб.</span>
				</div>
				<span class="icon-arrow-right"></span>
				<div class="buy-card-item">
					<span class="text">За каждого приглашенного вы сразу</span><br><span class="text">получите</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">{{ \App\Http\Controllers\RefController::ADD_MONEY_REF }}</span><span class="text">руб. и 1% с его побед</span>
				</div>
				<span class="icon-arrow-right"></span>

				<div class="buy-card-item">
				@if (\App\Http\Controllers\RefController::CHECK == 1 )
					<span class="text">Для активации купона вам</span><br><span class="text">необходима</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">1</span><span class="text">ставка</span>
				@else
				@if (\App\Http\Controllers\RefController::CHECK == 2 )
					<span class="text">Для активации купона вам</span><br><span class="text">необходимо</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">CSGO</span><span class="text">иметь</span>
				@else
				@if (\App\Http\Controllers\RefController::CHECK == 3 )
					<span class="text">Для активации купона вам</span><br><span class="text">необходимо</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">{{ \App\Http\Controllers\RefController::GAMESNEED }}</span><span class="text">игр и</span><span style="color: #d1ff78;font-size: 20px;font-weight: 600;margin-left: 5px;margin-right: 5px;">CSGO</span>
				@endif
				@endif
				@endif

			   </div>
			</div>
		</div>
	</div>
	<div class="about">
		<div class="page-main-block">
			<div class="page-block">
				<div class="coupon_bar">
					<span>Ваш купон: </span>
					<span id="balance_id">{{ $myref }}</span>
				</div>
			</div>
			<div style="overflow:hidden;margin-left: 100px;">
				<div class="page-main-block left" style="float: left;">
					<div class="page-block">
						<div class="ref_balance2">
						<b style="font-weight:normal;font-size:17px;">Ваш Баланс</b><br>
						<span id="balance_id">{{ $u->money }}</span> Рублей
						</div>
					</div>
				</div>
				<div class="page-main-block left" style="float: left;  margin-left: 100px;">
					<div class="page-block">
						<div class="ref_balance2">
						<b class="pribilj">Ваша прибыль</b><br>
						<span id="balance_id">{{ $u->refprofit }}</span> Рублей
						</div>
					</div>
				</div>
				<div class="page-main-block left" style="float: left;  margin-left: 100px;">
					<div class="page-block">
						<div class="ref_balance2">
						<b class="people_count">Refferals</b><br>
						<span id="balance_id">{{ $u->refcount }}</span> 
						</div>
					</div>
				</div>	
				<center>
				@if($u->refstatus != 1)
				<form action="/getcoupon" method="GET" style=" float: left; margin-top: 20px; margin-left: 76px;" >
					<div class="form">
						<input textarea name="idd" cols="50" maxlength="17" placeholder="КУПОН" autocomplete="off" class="coupon_post"></textarea>
						<input type="hidden" name="refid" id="desc" value={{ $u->steamid64 }}>
						<input type="submit" id="submitref" class="btn-add-balance2" value="Активировать">
					</div>
				</form> 
				@endif
				<form action="/setcoupon" method="GET" style=" float: left; margin-top: 20px; margin-left: @if($u->refstatus != 1) 25px @else 255px @endif;" >
					<div class="form">
						@if($u->refkode == NULL)
						<input textarea name="idd" cols="50" maxlength="17" placeholder="КУПОН" autocomplete="off" class="coupon_post"></textarea>
						@else
						<input textarea name="idd" cols="50" maxlength="17" placeholder="{{ $myref }}" value="{{$myref}}" autocomplete="off" class="coupon_post"></textarea>
						@endif
						<input type="hidden" name="refid" id="desc" value={{ $u->steamid64 }}>
						<input type="submit" id="submitref" class="btn-add-balance2" value="Создать свой">
					</div>
				</form> 
				</center>
			</div>
		</div>
	</div>

	<div class="title-block">
		<h2 style="color: #ffffff;">
			Приглашенные
		</h2>
	</div>
	@if($referals != NULL )
	<div class="user-winner-block" style="display: block;">
		<div class="user-winner-table">
			<table>
				<thead>
					<tr>
						<td>ID</td>
						<td class="winner-name-h">Профиль</td>
						<td>SteamID64</td>
						<td>Пригласил</td>
						<td>Баланс</td>
					</tr>
				</thead>
				@forelse($referals as $userl)
				<tbody>
					<tr>
						<td class="winner-count">
							<a href="/user/{{$userl->steamid64}}" style="color: #b3e5ff;"><div class="count-block" >#{{$userl->id}}</div></a>
						</td>
						<td class="winner-name" >
							<div class="user-ava"><img src="{{$userl->avatar}}"></div>
							<a href="/user/{{$userl->steamid64}}" style="color: #b3e5ff;"><span style="max-width:230px;">{{$userl->username}}</span></a>
						</td>
						<td class="participations">{{$userl->steamid64}}</td>
						<td class="win-count">{{$userl->refcount}}</td>
						
						<td class="participations">{{$userl->money}}</td>
					</tr>
				</tbody>
				@empty
				<br><center><h1 style="color: #FFF; font-weight: 300;">Рефералы отсутствуют!</h1></center>
				@endforelse
			</table>
		</div>
	</div>
	@else
	<br><center><h1 style="color: #FFF; font-weight: 300;">Рефералы отсутствуют!</h1></center>
	@endif
	
@endsection