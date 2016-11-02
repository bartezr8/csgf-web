@foreach(json_decode($bet->items) as $i)
@if(!isset($i->img))
	<div class="deposit-item {{ $i->rarity }}" market_hash_name="" title="{{ $i->name }}" data-toggle="tooltip">
		<div class="deposit-item-wrap">
				<div class="img-wrap"><img
					src="https://steamcommunity-a.akamaihd.net/economy/image/class/{{ \App\Http\Controllers\GameController::APPID }}/{{ $i->classid }}/101fx100f">
				</div>
		</div>
		<div class="deposit-price">{{ $i->price }} <span>руб</span>
		</div>
	</div>
@endif
@endforeach