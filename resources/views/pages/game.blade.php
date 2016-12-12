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