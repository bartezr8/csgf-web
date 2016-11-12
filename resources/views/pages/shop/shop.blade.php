@extends('layout')

@section('content')
    <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
    <script src="{{ $asset('assets/js/shop.js') }}"></script>

    <div class="title-block">
        <h2>
            Магазин
        </h2>
    </div>
    <div class="buy-cards-container" style="padding-top: 10px;">
        <div class="buy-cards-block">
            <div class="user-profile-container" style="padding-top: 0px; width: 165px; margin: 0px 0px 0px; float: left; margin-right: 10px;">
                <div class="user-profile-head" style="padding: 5px 5px 5px;">
                    <div class="left-block" style="width: inherit;">
                        <div class="user-info" style="padding-top: 0px; margin-right: 0px;">
                            <div class="reputation-container">
                                Уровень:
                                <div class="reputation-block">
                                    {{ $betssum }}
                                    <a id="user-level-btn" class="popover-btn"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="msg-wrap" style="width:835px; float: left;">
                <div class="icon-warning"></div>
                <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">На вашем аккаунте есть средства за которые вы можете покупать предметы или карточки!</div>
            </div>
        </div>
        <div style="overflow: hidden;">
            <div class="buy-cards-block" style="margin-top: 0px;">
                <div class="shop-item-filters">
                    <div class="left-totalitems">
                       Ваши выбранные предметы | Предметов: <span id="cart-total">0</span> | Сумма: <span id="cart-total-price">0</span>
                    </div>
                    <a href="{{ route('cards-history') }}" class="btn-history">История обменов</a>
                    <a class="btn-buy" id="get-cart">Купить предметы</a>
                </div>
            </div>
            <div id="cart-list" class="cart-list" style="margin-right: -25px;display: block;"></div>
            <div class="buy-cards-block" style="margin-top: 0px;">
                <div class="shop-item-filters">
                    <div class="left-totalitems">
                        Найдено предметов: <span id="filter-total">0</span> / <span id="items-total">{{ \App\Shop::countItems() }}</span>
                    </div>
                    <select class="shop-selector" style="margin-left: 10px;" id="exterior_all">
                        <option id="select_opt" value="">Любое качество</option>
                        <option id="select_opt" value="Прямо с завода">Прямо с завода</option>
                        <option id="select_opt" value="Немного поношенное">Немного поношенное</option>
                        <option id="select_opt" value="После полевых">После полевых</option>
                        <option id="select_opt" value="Поношенное">Поношенное</option>
                        <option id="select_opt" value="Закаленное в боях">Закаленное в боях</option>
                        <option id="select_opt" value="Не покрашено">Не покрашено</option>
                    </select>
                    <div class="search-form">
                        <span class="search-btn"></span>
                        <input id="searchInput" type="text" placeholder="Поиск по названию">
                    </div>
                    <div class="price-form">
                        Цена:
                        от <input id="priceFrom" type="text" placeholder="0">
                        до <input id="priceTo" type="text" placeholder="0">
                    </div>
                </div>
            </div>
            <div id="items-list" class="items-list" style="display: block;"></div>
        </div>
        <script type="text/template" id="item-template">
            <div class="deposit-item <%= className %> up-<%= className %>" id="deposit-item_<%= id %>" data-id="<%= id %>">
                <div class="deposit-item-wrap">
                    <div class="img-wrap"><img src="<%= image %>" alt="" title=""/></div>
                    <div class="name"><%= name %></div>
                    <div class="deposit-price"><%= price %> <span>руб</span></div>
                    <div class="deposit-exterior"><%= shortexterior %></div>
                    <div class="deposit-count">x<%= count %></div>
                </div>
            </div>
        </script>
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
    <script>
        function updateBalance() {
            $.post('/getBalance', function (data) {
                $('.userBalance').text(data);
            });
        }
    </script>
@endsection
