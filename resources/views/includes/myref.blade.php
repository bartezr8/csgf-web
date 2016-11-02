<div class="chatMessage clearfix <!--buzz-->">
	<a href="/user/{{ $usera->steamid64 }}" target="_blank">
		<img style="height: 32px; width: 32px;" src="{{ $usera->avatar }}">
	</a>
	<div class="login" href="/user/{{ $usera->steamid64 }}" target="_blank">{{ $usera->username }}</div>
	<a href="/game/{{ $gameid }}" target="_blank">
		<div class="body">Игра: {{ $gameid }} | Доля: {{ $money }} руб.</div>
	</a>
</div>