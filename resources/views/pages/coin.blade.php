@extends('layout')

@section('content')
<title>  {{ $title = 'Монетка | ' }}</title>
<link href="{{ $asset('assets/css/coin.css') }}" rel="stylesheet">
<script src="{{ $asset('assets/js/coin.js') }}"></script>
<div class="content">
    <div class="title-block">
        <h2 style="color: #ffffff;">Монетка</h2>
    </div>
    <div class="page-content"style="">
        <div class="add-balance-block" style="text-align: center;padding: 12px 0px 10px;border-bottom: 1px solid #3D5260;">
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                БЕЗ КОМИССИИ
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                Минимальная ставка:
                <span class="" style="font-size: 20px;font-weight: 600;color: #d1ff78;">0.01 </span> <div class="price-currency" style="display: inline;text-transform: uppercase;font-size: 11px;">руб.</div>
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                Ваш баланс:
                <span class="userBalance" style="font-size: 20px;font-weight: 600;color: #d1ff78;">{{ $u->money }} </span> <div class="price-currency" style="display: inline;text-transform: uppercase;font-size: 11px;">руб.</div>
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="input-group" style="display: inline-block;vertical-align: middle;">
                <input type="text" style="padding: 0 20px 0px 10px;width: 90px;height: 30px;display: inline-block;vertical-align: middle;background-color: rgba(27,42,53,0.68);border: 1px solid rgba(93,103,113,0.71);color: #b7d5e7;" id="coin_sum" pattern="^[ 0-9.]+$" maxlength="5" placeholder="Cумма">
                <button type="submit" class="btn-add-balance" id="coin_bet">Создать</button>
            </div>
        </div>
        <script type="text/template" id="coin-template">
            <tr id="coin_<%= id %>">
                <td class="participations">
                    <div class="count-block" id="sum"><%= sum %></div>
                </td>
                <td id="first" class="winner-name" >
                    <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                    <span id="user-name" style="max-width:200px;"><%= name %></span>
                </td>
                <td class="participations">
                    <button type="submit" class="btn-add-balance" id="coin_tp" onclick=" coin_bet( <%= id %> ); " >Участвовать</button>
                    <div class="flip-container" id="f1" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flip-container" id="f2" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flip-container" id="f3" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td id="second" class="winner-name" >
                    <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                    <span id="user-name" style="max-width:200px;">...</span>
                </td>
                <td class="participations">
                    <div class="count-block" ><%= sum %></div>
                </td>
            </tr>
        </script>
        <div class="user-winner-block">
            <div class="user-winner-table" style="padding-bottom: 10px;">
                <table>
                    <thead>
                        <tr>
                            <td>Сумма (руб.)</td>
                            <td class="winner-name-h" style="text-align: center; padding-left: 0px; width:250px;">Создатель</td>
                            <td>Игра</td>
                            <td class="winner-name-h" style="text-align: center; padding-left: 0px; width:250px;">Участник</td>
                            <td>Сумма (руб.)</td>
                        </tr>
                    </thead>
                    <tbody id="cointable">
                    @if($coingames != NULL )
                    @forelse($coingames as $game)
                        <tr id="coin_{{ $game['id'] }}">
                            <td class="participations">
                                <div class="count-block" id="sum">{{ $game['sum'] }}</div>
                            </td>
                            <td id="first" class="winner-name" >
                                <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                <span id="user-name" style="max-width:200px;">{{ $game['name'] }}</span>
                            </td>
                            <td class="participations">
                                <button type="submit" class="btn-add-balance" id="coin_tp" onclick=" coin_bet( {{ $game['id'] }} ); " >Участвовать</button>
                                <div class="flip-container" id="f1" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-container" id="f2" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-container" id="f3" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td id="second" class="winner-name" >
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                <span id="user-name" style="max-width:200px;">...</span>
                            </td>
                            <td class="participations">
                                <div class="count-block" >{{ $game['sum'] }}</div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    @else
                    @endif
                    </tbody>
                </table>
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