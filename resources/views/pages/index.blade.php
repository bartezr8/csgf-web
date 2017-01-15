@extends('layout')
@section('content')

<script>
    var have_gift = {{ $have_gift }};
</script>

<link href="{{ $asset('assets/css/ref.css') }}" rel="stylesheet">
<div class="advert-banner">
    <div style="display: inline-block;"><b>ВНИМАНИЕ!</b> ЕСЛИ ХОТИТЕ <b>ПОДДЕРЖАТЬ РУЛЕТКУ</b>: НА ЭТОЙ СТРАНИЦЕ В ЯНДЕКСЕ НАЙДИТЕ НАШУ РУЛЕТКУ И ОТКРОЙТЕ!!!</div>
    <a href="{{ route('rand_url') }}" target="_blank">КЛИК СЮДА</a>
</div>

@if(config('mod_game.gifts'))
<div class="buy-cards-container" style="padding-top: 10px;">
    <div class="buy-cards-block" style="text-align:center;">
        <div style="float: left; display: inline-block">
            <div class="buy-card-item" style="float: left; margin-top: 7px;">
                <span class="cards-price-currency">Подарки<br>получили</span>
            </div>
            <span class="icon-arrow-right"style="float: left;"></span>
            <div class="last-gout-block" id="last-gout-block" style="float: left; display: block; overflow: hidden; width: 865px; height: 52px;">
                @if($gifts != NULL )
                    @forelse($gifts as $gift)
                        <img class="giftwinner" title="{{ $gift['game'] }} | {{ $gift['price'] }}"  style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="{{ $gift['avatar'] }}" class="scale-in">
                    @empty
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    $('.giftwinner').tooltip({
        html: true,trigger: 'hover',delay: {show: 500,hide: 500},
        title: function() {
            var text = $(this).data('old-title');
            return '<div class="tooltip-title"><span>' + text + '</span></div>';
        }
    });
</script>
@endif
<div class="panel-winner" id="panel-winner" style="/*display: none;*/">
    <div class="lw">
        <div class="lw-text">Последний победитель</div>
        <div id="lw" style="background: url(assets/img/lw.png) no-repeat 0px rgba(87, 62, 72, 0);">
            <div id="lw-name"><a href="/user/" class="color-yellow"></a></div>
            <div id="lw-avatar"><img src="{{ $asset('assets/img/blank.jpg') }}" alt="" title=""></div>
            <div class="chanse_win" style="margin-top: 5px;" id="lw-chance">Шанс: <span class="down-text">???%</span></div>
            <div class="chanse_win" id="lw-money">Сумма выигрыша: <span class="down-text">??? Р</span></div>
        </div>
    </div>
    <div class="ml">
        <div class="lw-text">Самые везучие</div>
        <div class="mltd" id="mltd">
            <div class="ml-top">За день</div>
            <div class="ml-avatar" id="mltd-avatar"><img src="{{ $asset('assets/img/blank.jpg') }}" alt="" title=""></div>
            <div class="ml-name" id="mltd-name">???</div>
            <div class="chanse_win" id="mltd-chance">Шанс: <span class="down-text">???%</span></div>
            <div class="chanse_win" id="mltd-money">Выигрыш: <span class="down-text">??? Р</span> </div>
        </div>
        <div style="width: 1px; height: 170px; float: left; background-color: #2F5463;"></div>
        <div class="mlf" id="mlf">
            <div class="ml-top">За неделю</div>
            <div class="ml-avatar" id="mlf-avatar"><img src="{{ $asset('assets/img/blank.jpg') }}" alt="" title=""></div>
            <div class="ml-name" id="mlf-name">???</div>
            <div class="chanse_win" id="mlf-chance">Шанс: <span class="down-text">???%</span></div>
            <div class="chanse_win" id="mlf-money">Выигрыш: <span class="down-text">??? Р</span> </div>
        </div>
    </div>
    <div class="block-win">
        <div class="lw-text" style="margin-bottom: 0px;">Список призов</div>
        <div class="win-block" id="win-block" style="display: block; height: 180px;">
            <div class="purchase-history-table">
                <table>
                    <thead>
                    <tr>
                        <th>Игра</th>
                        <th>Цена</th>
                    </tr>
                    </thead>
                    <tbody id="bgifts" style="font-size: 10px;">
                    @forelse($bgifts as $gift)
                        <tr id="gifts_{{ $gift->id }}">
                            <td>@if( $gift->sold ) <s style="color: rgba(154, 154, 154, 0.5);"> @endif {{ $gift->game_name }} @if( $gift->sold ) </s> @endif</td>
                            <td>@if( $gift->sold ) <s style="color: rgba(154, 154, 154, 0.5);"> @endif {{ $gift->store_price }} @if( $gift->sold ) </s> @endif</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Призов в базе нет</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="pagination-history">
                </div>
            </div>
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
                <div class="item-bar-text"><span>{{ $game->items }}<span style="font-weight: 100;"> / </span>{{ config('mod_game.game_items') }}</span> {{ trans_choice('lang.items', $game->items) }}</div>
                <div class="item-bar" style="width: {{ ($game->items/config('mod_game.game_items'))*100}}%;"></div>
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
<div id="depositButtonsBlock" class="additional-block-wrap" style="">
    <div id="depositButtons" class="additional-container">
        <div class="participate-block" style="border-bottom: 1px solid #2F5463;">
            @if(Auth::guest())
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
                <a href="/login" class="add-deposit" style="padding: 10px 40px;">принять участие</a>
            @else
                <div class="participate-block participate-logged">
                    <div style="float: left">
                        <span class="icon-arrow-right" style="margin: 0px 15px 0px -15px;"></span>
                        <div class="participate-info">
                            Вы внесли <span id="myItemsCount" style="color: #d1ff78;">{{ $user_items }}<span style="font-size: 12px; color: #b3dcf9;"> {{ trans_choice('lang.items', $user_items) }}</span></span><br>
                            ваш шанс на победу: <span id="myChance" style="color: #d1ff78;">{{ $user_chance }}%</span>
                        </div>
                    </div>
                    <div style="float: right;">
                        <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                        <div class="participate-info">
                            Баланс <span class="userBalance" style="color: #d1ff78;">{{ $u->money }}</span>
                        </div>
                        <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
                        <div id="tbet" class="input-group" style="display: inline-block;" >
                            <input type="text" id="tsum" placeholder="0.00" style="width:80px">
                            <button type="submit" style="width: 90px;padding: 0px 9px;" class="btn-add-balance @if(empty($u->accessToken)) no-link @endif" onclick="tbet()" >Поставить</button>
                        </div>
                        <span class="icon-arrow-right" style="margin: 0px 0px 0px 0px;"></span>
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
<script>
    function tbet(){
        $.post('{{route('add.ticket')}}',{sum:$('#tsum').val()}, function(data){
            updateBalance();
            return $.notify(data.text, data.type);
        });
    }
</script>
<div style="display: none;">
    <div class="box-modal affiliate-program" id="giftModal">
        <div class="box-modal-head">
            <div class="box-modal_close arcticmodal-close"></div>
        </div>
        <div class="box-modal-content">
            <div class="content-block">
                <div class="title-block">
                    <h2>Ваш подарок</h2>
                </div>
            </div>
            <div class="b-modal-cards" style="border: none; width: 609px; border-radius: 0px;" id="cardDepositModal">
                <div class="box-modal-container">
                    <div class="box-modal-content">
                        <div class="add-balance-block">
                            <div class="balance-item" id="game_name">
                                {{ $game_name }}
                            </div>
                            <span class="icon-arrow-right"></span>
                            <div class="balance-item">
                                <span id="store_price">
                                    {{ $store_price }}
                                </span>
                            </div>
                            <span class="icon-arrow-right"></span>
                            <a class="btn-gift" href="/gifts/receive" target="_blank" style="float: none;" id="depUrl">Забрать</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection