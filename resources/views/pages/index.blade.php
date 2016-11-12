@extends('layout')
@section('content')
<link href="{{ $asset('assets/css/ref.css') }}" rel="stylesheet">
<!--<script src="{{ $asset('assets/js/dep.js') }}"></script>-->
    <div class="advert-banner">
        <div style="display: inline-block;"><b>ВНИМАНИЕ!</b> ЕСЛИ ХОТИТЕ <b>ПОДДЕРЖАТЬ РУЛЕТКУ</b>: НА ЭТОЙ СТРАНИЦЕ В ЯНДЕКСЕ НАЙДИТЕ НАШУ РУЛЕТКУ И ОТКРОЙТЕ!!!</div>
        <a href="{{ $url }}" target="_blank">КЛИК СЮДА</a>
    </div>
    <div class="panel-winner" id="panel-winner" style="/*display: none;*/">
        <!-- Последний  победитель -->
        <div class="lw">
            <div class="lw-text">Последний победитель</div>
            <div id="lw" style="background: url(assets/img/lw.png) no-repeat 0px rgba(87, 62, 72, 0);">
                <div id="lw-name"><a href="/user/" class="color-yellow"></a></div>
                <div id="lw-avatar"><img src="http://csgf.ru/assets/img/blank.jpg" alt="" title=""></div>
                <div class="chanse_win" style="margin-top: 5px;" id="lw-chance">Шанс: <span class="down-text">???%</span></div>
                <div class="chanse_win" id="lw-money">Сумма выигрыша: <span class="down-text">??? Р</span></div>
            </div>
        </div>
        <!--     Самые удачливые                                                                       -->
        <div class="ml">
            <div class="lw-text">Самые удачливые</div>
            <div class="mltd" id="mltd">
                <div class="ml-top">За день</div>
                <div class="ml-avatar" id="mltd-avatar"><img src="http://csgf.ru/assets/img/blank.jpg" alt="" title=""></div>
                <div class="ml-name" id="mltd-name">???</div>
                <div class="chanse_win" id="mltd-chance">Шанс: <span class="down-text">???%</span></div>
                <div class="chanse_win" id="mltd-money">Выигрыш: <span class="down-text">??? Р</span> </div>
            </div>
            <div style="width: 1px; height: 170px; float: left; background-color: #2F5463;"></div>
            <div class="mlf" id="mlf">
                <div class="ml-top">За неделю</div>
                <div class="ml-avatar" id="mlf-avatar"><img src="http://csgf.ru/assets/img/blank.jpg" alt="" title=""></div>
                <div class="ml-name" id="mlf-name">???</div>
                <div class="chanse_win" id="mlf-chance">Шанс: <span class="down-text">???%</span></div>
                <div class="chanse_win" id="mlf-money">Выигрыш: <span class="down-text">??? Р</span> </div>
            </div>
        </div>
        <!-- Блок -->
        <div class="block-win">
        <div class="win-block" id="win-block" style="display: block;">
            <!--div style="padding-top: 20px;font-size: 14px;color: #fff;text-align: center;text-transform: uppercase;">Пользователи онлайн:</div>
            <div style="margin: 12px; margin-top: 21px;"></div-->
        </div>
        </div>
    </div>
    
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
                    <div class="item-bar-text"><span>{{ $game->items }}<span style="font-weight: 100;"> / </span>100</span> {{ trans_choice('lang.items', $game->items) }}</div>
                    <div class="item-bar" style="width: {{ $game->items }}%;"></div>
                </div>
                <div class="bar-text">или через</div>
                <div class="timer-new" id="gameTimer">
                    <span class="countMinutes">02</span>
                    <span class="countDiv">:</span>
                    <span class="countSeconds">00</span>
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
            <div class="right-block">
                <div class="newGemaText">новая игра через</div>
                <div class="timer-new" id="newGameTimer">
                    <span class="countMinutes">00</span>
                    <span class="countDiv">:</span>
                    <span class="countSeconds">00</span>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <!-- Chat -->

    <div id="chatHeader" style="display: none;">Чат</div>

    <div id="chatContainer" class="chat-with-prompt" style="display: none;box-shadow: 0 0 10px #1E2127;">
        <span id="chatClose" class="chat-close"></span>
        <div id="chatHeader">Чат</div>
        <div class="chat-prompt" id="chat-prompt">
            <div class="chat-prompt-top">Чат сайта:</div>
            <div class="chat-prompt-mid">
                <div style="margin-top:7px;margin-bottom: 9px;text-align: -webkit-auto;border-bottom: 1px solid #2D4455;">
                    <ul>
                        <li style="list-style: none;"><span class="title">В очереди:</span> <span style="color: #8bb629" id="count_trades">0</span> <span class="title">трейдов</span></li>
                        <li style="list-style: none; margin-top: 3px"><span class="title">Прием в среднем:</span> <span id="speed_trades" style="color: rgb(139, 182, 41);">5.5</span> <span class="title">секунд</span></li>
                        @if(!Auth::guest())
                        <li style="list-style: none; margin-top: 3px"><span class="title">Ваша комиссия:</span> <span id="my_comission" style="color: rgb(139, 182, 41);">10</span> <span class="title">%</span></li>
                        @endif
                    </ul>
                </div>
                <!--div style="margin-top:7px;margin-bottom: 9px;">
                    На нашем сайте присутствует система по которой с выигрыша каждого приглашенного вами человека вам начисляеся 1 процент валютой сайта (карточками)
                </div-->
            </div>
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
                        <?php for($i = 1; $i< 514; $i++)echo "<img src=\"/assets/img/smiles/smile (".$i.").png\" id=\"smile\" style=\"background:none;\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
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
    <div id="depositButtonsBlock" class="additional-block-wrap" style="">

        <div id="depositButtons" class="additional-container">
            @if(Auth::guest())
            <div class="participate-block" style="border-bottom: 1px solid #2F5463;">
                <span class="icon-arrow-right"></span>
                <p>
                    чем <span class="color-lightblue">дороже</span> предметы Вы ставите,<br>
                    тем <span class="color-lightblue">выше</span> шанс на победу
                </p>
                <span class="icon-arrow-right"></span>
                <p>
                    Победитель определится когда наберется<br>
                    <span class="color-lightblue">100 предметов</span> или пройдет <span class="color-lightblue">120 секунд</span>
                </p>
                <span class="icon-arrow-right" style="margin: 0 20px;"></span>
                <a href="/login" class="add-deposit" style="/*margin: 10px 4px 0px 0px;*/padding: 10px 40px;">принять участие</a>
                @else
                    <div class="participate-block participate-logged">
                        <div style="float: left">
                            <span class="icon-arrow-right" style="margin: 0px 15px 0px -15px;"></span>
                            <div class="participate-info">Вы внесли <span id="myItemsCount" style="color: #d1ff78;">{{ $user_items }}<span style="font-size: 12px; color: #b3dcf9;"> {{ trans_choice('lang.items', $user_items) }}</span></span><br>ваш шанс на победу: <span id="myChance" style="color: #d1ff78;">{{ $user_chance }}%</span></div>
                        </div>

                        <div style="float: right;">
                            <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                            <div class="participate-info">
                                Баланс <span class="userBalance" style="color: #d1ff78;">{{ $u->money }}</span>
                            </div>
                            <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>

                            <div id="tbet" class="input-group" style="display: inline-block;" >
                                <input type="text" id="tsum" placeholder="0.00" style="width:80px">
                                <button type="submit" style="width: 90px;padding: 0px 9px;" class="btn-add-balance" onclick="tbet()" >Поставить</button>
                            </div>

                            <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                            <!--div class="card-or-item">или</div-->
                            <a id="depositButton" href="{{ route('deposit') }}" target="_blank" class=" add-deposit @if(empty($u->accessToken)) no-link @endif">Внести предметы</a>

                            <span class="icon-arrow-left" style="margin: 0px 0px 0px 15px;"></span>
                        </div>
                    </div>
                @endif
            </div>
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
                 Мин. сумма депозита {{ config('mod_game.min_price') }} руб.<br>Макс. депозит - {{ config('mod_game.max_items') }} предметов.
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
    <div class="content-block" style="font-family: sans-serif;line-height: 130%;color: #FFF;">
        <div class="title-block">
            <h1 style="text-transform: uppercase;color: #E8FBFF;font-weight: 400;font-size: 17px;position: relative;top: 8px;margin: 0;display: inline-block;">КС ГО Рулетка для бомжей с минимальной ставкой 1 рубль</h1>
        </div>
        <br>
        CSGF.RU – сайт <strong>рулетка CS GO</strong> для бомжей и новичков которые любят <strong>халяву</strong>. Для того чтобы стать участником игры Вы просто должны авторизоваться на сайте через ваш аккаунт в Steam. При Авторизации мы получаем только данные от вашего профиля. Наша рулетке необходима только вашу ссылку на обмен чтобы отправить вам ваш выигрыш. Затем Вы спокойно можете вносить любой депозит как карточками, так и вещами CS GO.
        <br><br>
        На нашей мы не ограничиваем игроков максимальной суммой депозита, Вы можете внести 20 предметов, но их общая стоимость увеличивает ваши шансы на победу. Каждый игрок может внести как большое, так и малое количество вещей, но от суммы внесенного вами депозита зависят ваши шансы на победу относительно других игроков.
        <br><br>
        Играя на нашей лотереи <strong>CS GO для бомжей</strong> и <strong>новичков</strong> с <strong>минимальной ставкой 1 рубль</strong>, Вы способны испытать свою удачу и проверить честность игры и получить крупную сумму без затруднений благодаря нашей открытой и прозрачной системе выбора победителя. Обмен с выигранными вещами отправляется нашими Steam ботами автоматически после завершения игры. 
        <br><br>
        Если у вас возникли вопросы или проблемы получения выигрыша на сайте, Вы всегда можете обратиться в нашу отзывчивую поддержку для получения моментального ответа на ваш запрос. Рулетка CS GO для бомжей и новичков с минимальной ставкой 1 рубль моментально решает все вопросы и проблеы связанные с непредвиденными обстоятельставми!
        <br><br>
        CSGF.RU – дает возможность игрокам вносить до 100 вещей в каждую игру. При этом не более 20 вещей от каждого игрока. Время одной игры на рулетке занимает 2 минуты. По ходу игры каждый игрок способен без затруднений внести вещи для принятия участия в розыгрыше. 
        <br><br>
        За каждый внесенный предмет игроку даются билеты. Победителем становиться обладатель именно победного билета. Наша рулетка обладает прозрачной системой выбора победного билета. Убедиться в этом Вы можете, посмотрев число раунда которое генерируется перед началом игры и шифруется в ключ MD5. Этот ключ выводиться на главной странице рулетки CS GO для бомжей и новичков для подтверждения прозрачности системы.
        <br>
    </div>

        @if(!Auth::guest())        
            
            <div style="display: none;">
                <div class="box-modal b-modal-cards" id="cardDepositModal">
                    <div class="box-modal-container">
                        <div class="box-modal_close arcticmodal-close"></div>


                        <div class="box-modal-content">
                            <div class="add-balance-block">
                                <div class="balance-item">
                                    Ваш баланс:
                                    <span class="userBalance">{{ $u->money }} </span> <div class="price-currency">рублей</div>
                                </div>

                                <span class="icon-arrow-right"></span>

                                <div id="tbet" class="input-group">
                                    <input type="text" id="tsum" placeholder="Введите сумму">
                                    <button type="submit" class="btn-add-balance" onclick="tbet()" >Поставить</button>
                                </div>
                            </div>
                            <div class="cards-cont">
                                <div class="msg-wrap" style="margin-bottom: -17px;">
                                    <div class="icon-warning"></div>
                                    <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">Балланс пополняется разными способами в <a href="{{ route('shop') }}" target="_blank">магазине</a>!</div>
                                </div>
                            </div>
                            

                            <!--div class="box-modal-footer">
                                <div class="msg-wrap" style="position: relative;">
                                    <div class="close-eto-delo box-modal_close" style="top: 6px; right: 6px; opacity: 0.8;"></div>
                                    <div class="msg-green" style="margin-left: 12px;margin-top: 20px;">
                                        <h2>Для чего нужны карточки?</h2>
                                        <p>Депозит карточками не чем не отличается от депозита скинами CS:GO.</p>
                                        <p>То есть, например, если Вы внесете депозит карточкой номиналом в 100 руб, это будет тоже самое, как будто бы Вы внесли депозит скинами CS:GO на 100 руб.</p>
                                        <p>Карточки не теряются в стоимости и моментально вносятся в игру без задержек.</p>
                                    </div>
                                </div>
                            </div-->
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: none;">
                <div class="box-modal affiliate-program" id="card-popup">
                    <div class="box-modal-head">
                        <div class="box-modal_close arcticmodal-close"></div>
                    </div>
                    <div class="box-modal-content">
                        <div class="content-block">
                            <div class="title-block">
                                <h2>Карточки CSGOFEAR.RU</h2>
                            </div>
                        </div>
                        <div class="text-block-wrap">
                            <div class="text-block">
                                <p class="lead-big">Карточки – это внутренняя валюта на нашем сайте
                                    <br>Карточки вносят в игру вместо скинов CS:GO.</p>
                                <p class="lead-big" style="margin: 0px -20px 15px;background: rgba(20, 34, 41, 0.5);padding: 15px;-webkit-box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);color: rgb(179, 218, 179);"> Депозит карточками не чем не отличается от депозита скинами CS:GO.
                                    <br> То есть, например, если Вы внесете депозит карточкой номиналом в 100 руб. это будет тоже самое, как будто бы Вы внесли депозит скинами CS:GO на 100 руб.
                                    <br>
                                <p class="lead-normal" style="margin-bottom: 10px;">- Карточки моментально вносятся в раунд без задержек. То есть Вы можете внести депозит карточками даже за 1 секунду до конца игры;</p>
                                <p class="lead-normal" style="margin-bottom: 10px;">- Можно играть на сайте не имея скинов;</p>
                                <p class="lead-normal" style="margin-bottom: 10px;">- Пользователи продолжают играть на сайте, даже когда есть проблемы со стимом и отключен прием депозитов скинами.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
<script>
    function tbet(){
        $.post('{{route('add.ticket')}}',{sum:$('#tsum').val()}, function(data){
            updateBalance();
            return $.notify(data.text, data.type);
        });
    }
</script>
@endsection