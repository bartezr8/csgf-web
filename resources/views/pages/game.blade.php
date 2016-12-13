@extends('layout')

@section('content')
    <div class="content-block">
        <div class="msg-wrap" style="display: none;">
            <div class="icon-shield-red"></div>
            <div class="msg-red msg-mini">
                У нас все по-честному. Победитель на нашем сайте определяется в прямом эфире через сервис Random.org
                <a href="/fairplay" class="btn-more arrow-sm">узнать подробнее</a>
            </div>
        </div>

        <div class="game-info-wrap" style="margin-bottom: 20px;">
            <div class="game-info" style="height: 186px;">
                <div class="game-info-title">
                    <div class="left-block">
                        <div class="text-wrap">
                            <span class="color-orange">игра</span>
                            <span class="weight-normal">#</span>
                            <span class="color-white">{{ $game->id }}</span>
                        </div>
                    </div>
                    <span class="divider weight-normal"></span>
                    <div class="right-block">
                        <div class="text-wrap">
                            <span class="color-orange">банк</span>
                            <span class="weight-normal">:</span>
                            <span class="color-white">
                                {{ round($game->price) }}
                                <span class="money" style="color: #b3e5ff;">руб</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="game-round-finish">
                    <div class="game-info-additional">
                        <div class="left-block">
                            <div class="additional-text">
                                Победный билет: <span class="winning-sum">#{{ $game->ticket }}</span>
                                <span class="text-small">(ВСЕГО: {{ $bankTotal = $game->price * 100 }})</span>
                                <a href="/fairplay/{{ $game->id }}" class="check-btn-blue">проверить</a> <br>

                                Победил игрок: <div class="img-wrap"><img src="{{ $game->winner->avatar }}"></div> <a href="/user/{{ $game->winner->steamid64 }}" class="link-user color-yellow">{{ $game->winner->username }}</a>
                                <span class="text-small">(ШАНС: {{ \App\Http\Controllers\GameController::_getUserChanceOfGame($game->winner, $game) }}%)</span> <br>

                                Выигрыш:
                                <span class="winning-sum">{{ $game->price }}</span>
                                <span class="text-small">РУБ</span>
                            </div>
                        </div>

                        <!--<div class="round-finish-title">Игра завершена</div>!-->

                        <div class="right-block">
                            <a href="/game/{{ $game->id - 1 }}" class="btn-back-home">Посмотреть предыдущую игру</a>
                            <a href="/" class="btn-back-home">Вернуться на главную страницу</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <div id="usersChances" class="coursk">
            <div id="showUsers" class="iusers active" title="Показать игроков"></div>
            <div class="arrowscroll left"></div>
            <div class="current-chance-block users">
                <div class="current-chance-wrap">
                    @foreach(json_decode($chances) as $info)
                        <div class="current-user" title="" data-original-title="{{ $info->username }}"><a class="img-wrap" href="/user/{{ $info->steamid64 }}" target="_blank"><img src="{{ $info->avatar }}"></a><div class="chance">{{ $info->chance }}%</div></div>
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
            <div class="arrowscroll right"></div>
            <div id="showItems" class="iskins" title="Показать предметы"></div>
        </div>

        <div id="errorBlock" class="msg-big msg-error" style="display: none;">
            <div class="msg-wrap">
                <h2>ВАШЕ ПРЕДЛОЖЕНИЕ ОБМЕНА ОТКЛОНЕНО!</h2>
                <p></p>
            </div>
        </div>

        <div class="msg-big msg-finish">
            <div class="msg-wrap">
                <h2>Игра завершилась!</h2>
                <a href="/fairplay" class="btn-fairplay">честная игра</a>
                <p>Число раунда: <span class="underline">{{ $game->rand_number }}</p>
                <div class="date">{{ $game->updated_at }}</div>
            </div>
        </div>

        <div id="deposits">
            @foreach($bets as $bet)
                @include('includes.bet')
            @endforeach
        </div>

        <div class="msg-big msg-start">
            <div class="msg-wrap">
                <h2>Игра началась!</h2>
                <a href="/fairplay" class="btn-fairplay">честная игра</a>
                <p>Хэш: <span class="underline">{{ md5($game->rand_number) }}</span></p>
                <div class="date">{{ $game->created_at }}</div>
            </div>
        </div>
    </div>
@endsection