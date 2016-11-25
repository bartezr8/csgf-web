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
		<form action="/fixgame" method="POST">
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
 <!-- Chat -->

    <div id="chatHeader" style="display: none;">Чат</div>

    <div id="chatContainer" class="chat-with-prompt" style="display: none;box-shadow: 0 0 10px #1E2127;">
        <span id="chatClose" class="chat-close"></span>
        <div id="chatHeader">Чат</div>
            <div class="chat-prompt" id="chat-prompt">
                <div class="chat-prompt-top">Чат сайта:</div>
            </div>
        <div id="chatScroll">
            <div id="messages">
            </div>
        </div>
        @if(!Auth::guest())
        <form action="#" class="chat-form">
            <textarea id="chatInput" maxlength="255" placeholder="Введите сообщение"></textarea>
            <div class="chat-actions">
                <a id="chatRules" class="chat-rules">Правила чата</a>
                
                <div class="smiles">
                    <div class="sub">
                        <?php for($i = 1; $i <= 505; $i++)echo "<img src=\"/assets/img/smiles/smile (".$i.").png\" id=\"smile\" style=\"background:none;\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
                    </div>
                    <span></span>
                </div>
                
            </div>
            <button class="chat-submit-btn">Отправить</button>
        </form>
        @else
        <a id="notLoggedIn" href="{{ route('login') }}">Войти через Steam</a>
        @endif

        <div style="display: none;">
            <div class="box-modal affiliate-program" id="chatRulesModal">
                <div class="box-modal-head">
                    <div class="box-modal_close arcticmodal-close"></div>
                </div>
                <div class="box-modal-content">
                    <div class="content-block">
                        <div class="title-block">
                            <h2>Правила чата</h2>
                        </div>
                    </div>
                    <div class="text-block-wrap">
                        <div class="text-block">
                            <div class="page-main-block" style="text-align: left !important;">
                                <div class="page-block">За чатом на сайте 24 часа в сутки, 7 дней в неделю, следит модератор, который банит пользователей в чате за нарушения правил</div>

                                <div class="page-mini-title">В чате запрещается:</div>
                                <div class="page-block">
                                    <ul>
                                        <li style="margin-bottom: 5px;">Спам; Спам своим рефералом.</li>
                                        <li style="margin-bottom: 5px;">Оскорблять других участников;</li>
                                        <li style="margin-bottom: 5px;">Оставлять ссылки на сторонние ресурсы;</li>
                                        <li style="margin-bottom: 5px;">Выпрашивать скины у других участников;</li>
                                        <li style="margin-bottom: 5px;">Писать слово подкрутка или обвинять админов в подкрутке;</li>
                                        <li style="margin-bottom: 0px;">Оставлять сообщения о предложении покупки, продажи или обмена скинов.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chat END -->
@endsection