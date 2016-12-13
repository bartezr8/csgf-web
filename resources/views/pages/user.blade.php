@extends('layout')

<!--<script type="text/javascript" src="https://steamcommunity-a.akamaihd.net/public/shared/javascript/shared_global.js?v=qjG6RzSe7HOJ&amp;l=russian"></script>-->

@section('content')
    <div class="user-profile-container">

    <div class="user-profile-head">

        <div class="user-avatar">
            <img src="{{ $avatar }}">
        </div>

        <div class="left-block">

            <div class="user-info">
                <div class="username">
                    {{ $username }}
                </div>
                <div class="reputation-container">
                    Лимит на вывод:
                    <div class="reputation-block">
                        {{ $slimit }}
                        <a id="user-level-btn" class="popover-btn"></a>
                    </div>
                </div>
				@if(!Auth::guest())
				@if($userId == $u->steamid64 )
				<div class="input-group" style="width: 43.8%;margin-top: -29px;margin-left: 235px;position: relative;">
					<input class="save-password-input" onchange="updatepassword(this.value)" type="text" pattern="^[a-zA-Z0-9]+$" placeholder="Пароль" maxlength="17" style="text-align: center;" value="">
				</div>
				@endif
				@endif
            </div>

            <div class="right-block">
                <ul class="list-reset">
                    <li>Игры: <span>{{ $games }}</span> </li>
                    <li>Победы: <span class="lightgreen">{{ $wins }}</span></li>
                    <li>Win rate: <span class="lightgreen">{{ $winrate }}%</span></li>
                    <li>Сумма раундов: <span class="currency-icon">{{ $totalBank }}</span></li>
                </ul>
            </div>
			@if(!Auth::guest())
			@if( $userId == $u->steamid64 )
				
			<div class="input-group" style="width: 75.8%; position: relative;">
			@if($u->trade_link)
			<input class="save-trade-link-input" type="text" placeholder="Введите вашу ссылку на обмен" value="{{$u->trade_link}}">
			@endif
			@if(!$u->trade_link)
			<input class="save-trade-link-input" type="text" placeholder="Введите вашу ссылку на обмен" >
			@endif
			<span class="save-trade-link-input-btn"></span>
			</div>
			<a class="getLink" href="http://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url" target="_blank">Узнать мою ссылку на обмен</a>
			@endif
			@if( $userId != $u->steamid64 )
			<div class="input-group">
				<a class="userLink" href="{{ $url }}" target="_blank" style="width:65%; display:-webkit-inline-box;">{{ $url }}</a>
				@if( $tradeurl != NULL )
				<a class="getLink" href="{{ $tradeurl }}" target="_blank">Отправить обмен</a>
				@endif
				<a class="getLink" onclick="sendMoney('{{ $userId }}')" target="_blank">Перевод</a>
				@if($u->is_moderator == 1 )
				<a class="getLink" href="/admin/user/{{ $userId }}" target="_blank">Управление</a>
				@endif
			</div>
			@endif
			@endif
			@if(Auth::guest())
			<div class="input-group">
				<a class="userLink" href="{{ $url }}" target="_blank">{{ $url }}</a>
			</div>
			@endif
        </div>

    </div>

    <div class="user-profile-content">
        <table>
            <tbody id="showMoreContainer">

            @foreach($list as $game)
            <tr>
                <td><a href="/game/{{ $game -> id }}" class="game-number">Игра <span>{{ $game -> id }}</span></a></td>
                <td class="round-money">{{ $game -> bank }}</td>
                <td class="game-status">
                    @if($game->win == 1)
						@if($game->chance == 100)
							<span class="prize-status status-wait">Бонус</span>
						@else
							<span class="prize-status status-win">Победа</span>
						@endif
                    @elseif($game->win == -1)
                    <span class="prize-status status-wait">Не завершена</span>
                    @else
                    <span class="prize-status status-err">Проигрыш</span>
                    @endif
                </td>
                <td class="chance-td"><div class="chance">с шансом <span>{{ $game->chance }}%</span></div></td>
                <td><a href="/game/{{ $game -> id }}" class="round-history">Посмотреть историю игры</a></td>
            </tr>
            @endforeach

            </tbody>
        </table>
    </div>
<script type="text/javascript">
function updatepassword(value) {
	$.ajax({
		url: '/updatepassword',
		type: 'POST',
		dataType: 'json',
		data: {
			value: value
		},
		success: function () {
			$.notify("Пароль изменен", {
				className: "success"
			});
		},
		error: function () {
			$.notify("Произошла ошибка. Попробуйте еще раз", {
				className: "error"
			});
		}
	});
}
</script>
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
                        <?php for($i = 1; $i<= 505; $i++)echo "<img id=\"smile\" class=\"smile-smile-_".$i."_\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
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