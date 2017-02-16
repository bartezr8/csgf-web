@extends('layout')
@section('content')
<link href="{{ $asset('assets/css/ref.css') }}" rel="stylesheet">

<div class="game-info-wrap">
    <div class="game-info" style="height: 161px;">
        <div class="game-info-title">
            <div class="left-block">
                <div class="text-wrap">
                    <span class="color-orange">игра</span>
                    <span class="weight-normal">#</span>
                    <span id="roundId" class="color-white">{{ $game->id }}</span>
                </div>
            </div>
            <span class="divider weight-normal"></span>
            <div class="right-block">
                <div class="text-wrap">
                    <span class="color-orange">банк</span>
                    <span class="weight-normal">:</span>
                    <span id="roundBank" class="color-white">{{ round($game->price) }} <span class="money" style="color: #b3e5ff;">руб</span></span>
                </div>
            </div>
        </div>
        <div id="barContainer" class="bar-container">
            <div class="item-bar-wrap">
                <div class="item-bar-text"><span>{{ count($game->users()) }}<span style="font-weight: 100;"> / </span>3</span> игроков</div>
                <div class="item-bar" style="width: {{ count($game->users())/3 }}%;"></div>
            </div>
            <div class="participate-block" style="border-bottom: 1px solid #2F5463;">
                @if(Auth::guest())
                    <div class="participate-block participate-logged">
                        <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                        <a href="/login" class="add-deposit" style="padding: 10px 40px;">Принять участие</a>
                        <span class="icon-arrow-left" style="margin: 0px 0px 0px 15px;"></span>
                    </div>
                @else
                    <div class="participate-block participate-logged">
                        <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                        <a id="depositButton" onclick="openWindow('{{ route('bdeposit') }}');" target="_blank" class=" add-deposit @if(empty($u->accessToken)) no-link @endif">Внести предметы</a>
                        <span class="icon-arrow-left" style="margin: 0px 0px 0px 15px;"></span>
                    </div>
                @endif
            </div>
        </div>
        <div id="usersCarouselConatiner" class="player-list" style="width: 20000px; display: none;">
            <ul id="usersCarousel" class="list-reset">
            </ul>
        </div>
    </div>
</div>
<div id="winnerInfo" class="game-info-additional" style="padding: 20px 0px 0px; display: none;">
    <div class="winner-info-holder" style="padding: 0px 5px 18px; display: none;">
        <div class="left-block">
            <div class="additional-text">
                Победный билет: <span class="color-green" id="winTicket">#0</span> <span class="text-small">(ВСЕГО: <span id="totalTickets">0</span>)</span> <a href="/fairplay/{{ $game->id }}" class="check-btn-empty">проверить</a><br/>
                Победил игрок: <div class="img-wrap"><img src=""></div> <a href="#" target="_blank" class="link-user color-yellow" id="winnerLink">login</a> <span class="text-small" id="winnerChance">(0)</span><br/>
                Выигрыш: <span class="winning-sum" id="winnerSum">0</span> <span class="text-small">РУБ</span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div id="depositButtonsBlock" class="additional-block-wrap" style="">
    <div id="depositButtons" class="additional-container">
        <div class="msg-wrap" style="width: 100%;display: inline-block;padding-top:1px">
            <div class="icon-warning"></div>
            <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">При победе вы получите не меньше чем поставили! Ваша комиссия зависит только от вас!</div>
        </div>
        <div class="deposit-confirm-head wait-msg" style="display: none;">
            <div class="left-block trade-text">
            </div>
            <div class="right-block">
                <div class="hourglass">подождите, ваш депозит обрабатывается</div>
            </div>
        </div>
        <div class="deposit-confirm-head error-msg" style="display: none;">
            <div class="left-block trade-text">
                <span style="color: #F9C2C2;">Ваш депозит был принят с ошибкой,</span> нажмите на кнопку "Внести в игру" для повторной обработки
            </div>
            <div class="right-block">
                <div id="chooseGameTradeBtn" class="adBtn greenBtn buzz" data-id="">Внести в игру</div>
            </div>
        </div>
        <div id="minDepositMessage" class="msg-wrap">
            <a href="https://steamcommunity.com/id/user/edit" target="_blank">
                <div class="black-txt-info " style="width: 33%;float: left;margin-right: 5px;">
                    Снизь комиссию на {{ config('mod_game.comission_site_nick') }}%!<br>Добавь в ник <b>{{ config('app.sitename') }}</b>
                </div>
            </a>
            <div class="black-txt-info " style="width: 33%;float: left;margin-right: 5px;">
                Макс. сумма депозита {{ config('mod_bich.max_price') }} руб.<br>Макс. депозит - {{ config('mod_bich.max_items') }} предметов.
            </div>
            <div class="black-txt-info " style="width: 33%;float: left;">
                Поставь первым!<br>Получи -{{ config('mod_game.comission_first_bet') }}% к комиссии!
            </div>
        </div>
    </div>
    <div id="usersChances" class="coursk" @if(count(json_decode($chances)) == 0) style="display: none; @endif">
        <div id="showUsers" class="iusers active" title="Показать игроков"></div>
        <div class="arrowscroll left"></div>
        <div class="current-chance-block users">
            <div class="current-chance-wrap">
                @foreach(json_decode($chances) as $info)
                    <div class="current-user" title="" data-original-title="{{ $info->username }}"><a class="img-wrap" href="/user/{{ $info->steamid64 }}" target="_blank"><img style="@if( $info->vip ) border: 1px dashed #F9FF2F; @endif" src="{{ $info->avatar }}"></a><div class="chance">{{ $info->chance }}%</div></div>
                @endforeach
            </div>
        </div>
        <div class="current-chance-block items" style="display: none;">
            <div class="current-chance-wrap">
                @foreach($bets as $bet)
                    @foreach(json_decode($bet->items) as $i)
                    @if(!isset($i->img))
                        <div class="deposit-item {{ $i->rarity }}" market_hash_name="" title="{{ $i->name }}" data-toggle="tooltip">
                            <div class="deposit-item-wrap">
                                    <div class="img-wrap"><img
                                        src="https://steamcommunity-a.akamaihd.net/economy/image/class/{{ config('mod_game.appid') }}/{{ $i->classid }}/101fx100f">
                                    </div>
                            </div>
                            <div class="deposit-price">{{ $i->price }} <span>руб</span>
                            </div>
                        </div>
                    @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        <div class="arrowscroll right" style="display: none;"></div>
        <div id="showItems" class="iskins" title="Показать предметы"></div>
    </div>
    <div id="errorBlock" class="msg-big msg-error" style="display: none;">
        <div class="msg-wrap">
            <h2>ВНИМАНИЕ! ОШИБКА ПРИ ДЕПОЗИТЕ</h2>
            <p></p>
        </div>
    </div>
    <div id="linkBlock" class="msg-big msg-offerlink" style="display: none;">
        <div class="msg-wrap">
            <h2>УКАЖИТЕ ВАШУ ССЫЛКУ НА ОБМЕН</h2>
            <div class="input-group">
                <input class="save-trade-link-input" style="margin-left: 115px;" type="text" placeholder="Введите тут вашу ссылку на обмен" />
                <span class="save-trade-link-input-btn"></span>
                <a class="getLink-index" href="http://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url" target="_blank">Узнать мою ссылку на обмен</a>
            </div>
        </div>
    </div>

    <div id="roundFinishBlock" class="msg-big msg-finish" style="display: none;">
        <div class="msg-wrap">
            <h2>Игра завершилась!</h2>
            <a href="/fairplay" class="btn-fairplay">честная игра</a>
            <p>Число раунда: <span class="underline number">0</span></p>
            <form action="https://api.random.org/verify" method="post" target="_blank" style="display: inline;">
                <input type="hidden" name="format" value="json" />
                <input type="hidden" name="random" value="" />
                <input type="hidden" name="signature" value="" />
                <a href="#" onclick="document.forms[0].submit(); return false;" class="check-btn-green">проверить</a>
            </form>
        </div>
    </div>
</div>
<div id="vd" style="display:none;border-bottom: 1px solid #2D4455;">
    <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Предворительный просмотр вашего обмена.</b></div>
    <div id="view_deposits"></div>
</div>
<div id="deposits">
    @foreach($bets as $bet)
        @include('includes.bet')
    @endforeach
</div>
<div id="roundStartBlock" class="msg-big msg-start">
    <div class="msg-wrap">
        <h2 style="margin-bottom: 9px;">Игра началась! Вносите депозиты!</h2>
        <span>ВНЕСИ ДЕПОЗИТ ПЕРВЫМ И ПОЛУЧИ -{{ config('mod_game.comission_first_bet') }}% КОМИССИИ!</span><br>
        
        <a href="/fairplay" class="btn-fairplay">честная игра</a>
        <p>Хэш: <span id="hash" class="underline">{{ md5($game->rand_number) }}</span></p>
        <div class="date">{{ $game->created_at }}</div>
    </div>
</div>
@endsection