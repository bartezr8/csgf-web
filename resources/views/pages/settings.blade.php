@extends('layout')

@section('content')
    <div id="linkBlock" class="msg-big msg-offerlink">
        <div class="msg-wrap">
            <h2>УКАЖИТЕ ВАШУ ССЫЛКУ НА ОБМЕН</h2>
            <div class="input-group">
				@if($u->trade_link)
                <input class="save-trade-link-input" style="margin-left: 115px;" type="text" placeholder="{{ $u->trade_link }}" />
				@endif
				@if(!$u->trade_link)
				<input class="save-trade-link-input" style="margin-left: 115px;" type="text" placeholder="Введите тут вашу ссылку на обмен" />
				@endif
                <span class="save-trade-link-input-btn"></span>
                <a class="getLink-index" href="http://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url" target="_blank">Узнать мою ссылку на обмен</a>
            </div>
        </div>
    </div>
@endsection