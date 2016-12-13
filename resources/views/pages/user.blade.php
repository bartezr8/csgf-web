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
				<div class="input-group" style="width: 40%;margin-top: -29px;margin-left: 235px;position: relative;">
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
@endsection