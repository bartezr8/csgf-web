@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\AdminController::TITLE_UP }}</title>

<link href="{{ $asset('assets/css/admin.css') }}" rel="stylesheet">
    <div class="admin-container">
        <div class="admin-top">
            <div class="logotype active">
			</div>
            <div class="admin-menu">
                <ul id="headNav" class="list-reset">
					<li class="faq"><a href="/admin/"><img src="/assets/img/stav.png" alt=""> Главная страница</a></li>
					<li class="faq"><a href="/admin/users/"><img src="/assets/img/user.png" alt=""> Пользователи</a></li>
					<li class="faq"><a href="/admin/am/"><img src="/assets/img/tp.png" alt=""> Антимат</a></li>
                    <li class="faq"><a href="/shop/admin/"><img src="/assets/img/php.png" alt=""> История обменов</a></li>
                </ul>
            </div>
        </div>
	</div>
<div class="content">
	<div class="title-block">
		<h2 style="color: #ffffff;">
			Панель Управления
		</h2>
	</div>
	@if($u->is_admin==1)
	<div class="support" >
		<form action="/fixgame" method="GET">
			<div style="width: 333px" class="nSend">
				<input type="text" name="game_id" cols="50" style="width: 115px" cols="50" placeholder="Номер игры" maxlength="18" autocomplete="off">
				<input type="submit" style="width: 218px" value="Переотправить Игру">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/fixtic" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="text" name="id" cols="50" style="width: 115px" cols="50" placeholder="Номер игры" maxlength="18" autocomplete="off">
				<input type="submit" style="width: 218px" value="Починить билеты">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/ctime" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="text" name="time" cols="50" style="width: 115px" cols="50" placeholder="Время" maxlength="18" autocomplete="off">
				<input type="submit" style="width: 218px" value="Назначить время">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
	</div>
	<div class="support" >
		<form action="/updateNick" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="submit" style="width: 333px" name="mute" value="Обновить Ники">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/clearredis" method="GET">
			<div style="width: 333px" class="nSend">
				<input type="submit" style="width: 333px" name="mute" value="Очистить редис ">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/updateShop" method="POST">
			<div style="width: 333px" class="nSend">
                <input type="text" name="id" cols="50" style="width: 115px" cols="50" placeholder="Время" maxlength="18" autocomplete="off">
				<input type="submit" style="width: 218px" value="Инвентарь бота (шоп)">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
	</div>
	<div class="support" >
		<form action="/winner" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="text" name="id" cols="50" style="width: 185px" cols="50" placeholder="Номер билета" maxlength="18" autocomplete="off">
				<input type="submit" style="width: 148px" value="Подкрутить">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
		<form action="/winnerr" method="POST">
			<div style="width: 666px" class="nSend">
				<input type="text" name="id" cols="50" style="width: 333px" cols="50" placeholder="Число раунда" maxlength="18" value="0.55" autocomplete="off">
				<input type="submit" style="width: 333px" value="Назначить число раунда">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>	
	</div>
	@else
	<div class="support" >
		<form action="/updateNick" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="submit" style="width: 333px" name="mute" value="Обновить Ники">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/clearchat" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="submit" style="width: 333px" name="mute" value="Очистить чат ">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
        <form action="/updateShop" method="POST">
			<div style="width: 333px" class="nSend">
				<input type="submit" style="width: 333px" name="mute" value="Обновить магазин">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>
	</div>
	@endif
</div>
@endsection