<div title="{{ $bet->msg }}" class="deposits-container @if($bet->id == \App\Http\Controllers\GameController::getLastBet()) vibro @endif" id="bet_{{ $bet->id }}">
    <div class="deposit-head">
        <div class="left-block">
            <div class="profile-img"><img @if($bet->vip) style="border: 1px dashed #F9FF2F;" @endif src="{{ $bet->user->avatar }}"></div>
            <ul class="list-reset">
                <li class="profile-block">
				<span class="profile-name"><a href="/user/{{ $bet->user->steamid64 }}" style="color: @if($bet->vip) #F9FF2F @else #b4fca6; @endif"><div id="username_{{ $bet->id }}">{{ $bet->user->username }}</div> </a></span> 
				@if($bet->user->steamid64 != 76561197960265728)
					<span class="profile-level" data-original-title="" title=""></span> <span class="deposit-count">внес {{ $bet->itemsCount }} {{ trans_choice('lang.items', $bet->itemsCount) }}</span></li>
				@else
					@if(!isset($i->img))
						<span class="profile-level" data-original-title="" title=""></span> <span class="deposit-count">внес {{ $bet->itemsCount }} {{ trans_choice('lang.items', $bet->itemsCount) }}</span></li>
					@else
						<span class="profile-level" data-original-title="" title=""></span> <span class="deposit-count">внес 1% с предыдущей игры:</span></li>
					@endif
				@endif
                <li class="deposit-sum">{{ $bet->price }} <span>руб</span></li>
                @if($bet->user->steamid64 != 76561197960265728)
					<li class="deposit-chance">(шанс: <span class="id-{{ $bet->user->steamid64 }}">{{ \App\Http\Controllers\GameController::_getUserChanceOfGame($bet->user, $bet->game) }}%</span>)</li>
				@endif
            </ul>
        </div>
        <div class="right-block">
		@if($bet->user->steamid64 != 76561197960265728)
            <div class="ticket-number" data-original-title="1 билет = 1 копейка" title="1 билет = 1 копейка">Билеты: от <span class="color-orange">#{{ $bet->from }}</span> до <span class="color-orange">#{{ $bet->to }}</span> <span class="help"></span>
            </div>
		@endif
        </div>
    </div>
    <div class="deposit-content">
        @foreach(json_decode($bet->items) as $i)
        <div class="deposit-item @if(!isset($i->img)){{ $i->rarity }} @else card up-card @endif" title="{{ $i->name }}" style="@if($bet->user->steamid64 == 76561197960265728) border: 1px solid #FFA000; @endif" data-toggle="tooltip">
            <div class="deposit-item-wrap">
				@if(isset($i->com))
					@if($i->com == true)<span class="comission">Комиссия</span>@endif
				@endif
                @if(!isset($i->img))
                    <div class="img-wrap"><img src="https://steamcommunity-a.akamaihd.net/economy/image/class/{{ config('mod_game.appid') }}/{{ $i->classid }}/101fx100f"></div>
                @else
                    <div class="img-wrap"><img style="@if(isset($i->style)) {{ $i->style }} @endif" src="{{ $asset($i->img) }}"></div>
                @endif
                </div>
                <div style="@if($bet->user->steamid64 == 76561197960265728) background: #000000; @endif" class="deposit-price">{{ $i->price }} <span>руб</span></div>
            </div>
        @endforeach
    </div>
</div>
