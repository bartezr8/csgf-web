@extends('layout')

@section('content')
    <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
    <div class="title-block">
        <h2>
            Магазин
        </h2>
    </div>
    <div class="buy-cards-container" style="padding-top: 10px;">
        <div class="buy-cards-block">
            <div class="user-profile-container" style="padding-top: 0px; width: 220px; margin: 0px 0px 0px; float: left; margin-right: 10px;">
                <div class="user-profile-head" style="padding: 5px 5px 5px;">
                    <div class="left-block" style="width: inherit;">
                        <div class="user-info" style="padding-top: 0px; margin-right: 0px;">
                            <div class="reputation-container">
                                Лимит вывода:
                                <div class="reputation-block">
                                    <div id="slimit">{{ $slimit }}</div>
                                    <a id="user-level-btn" class="popover-btn"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="msg-wrap" style="width:780px; float: left;">
                <div class="icon-warning"></div>
                <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">На вашем аккаунте есть средства за которые вы можете покупать предметы или карточки!</div>
            </div>
        </div>
        <div style="overflow: hidden;">
            <div class="buy-cards-block"  style="margin-top: 0px;">
                <div class="shop-item-filters">
                    <div class="left-totalitems">
                       Ваши выбранные предметы | Предметов: <span id="cart-total">0</span> | Сумма: <span id="cart-total-price">0</span>
                    </div>
                    <a href="{{ route('cards-history') }}" class="btn-history">История обменов</a>
                    <a class="btn-buy" id="get-cart">Купить предметы</a>
                    <a class="btn-inv" id="card_block" style="height: 30px;width: 30px;margin: 2px;" target="_blank">&#9760;</a>
                </div>
            </div>
            <div id="cart-list" class="cart-list" style="margin-right: -25px;display: block;overflow: auto;max-height: 464px;"></div>
            <div class="buy-cards-block" style="margin-top: 0px;">
                <div class="shop-item-filters" style="margin-bottom:0;">
                    <div class="left-totalitems">
                        Найдено предметов: <span id="filter-total">0</span> / <span id="items-total">{{ \App\Shop::countItems() }}</span>
                    </div>
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
                <div class="shop-item-filters">
                    <div class="left-totalitems">
                        Выберите вещи для покупки
                    </div>
                    <select class="shop-selector" style="margin-left: 20px;float: right;" id="exterior_all">
                        <option value="">Любое качество</option>
                        <option value="Прямо с завода">Прямо с завода</option>
                        <option value="Немного поношенное">Немного поношенное</option>
                        <option value="После полевых">После полевых</option>
                        <option value="Поношенное">Поношенное</option>
                        <option value="Закаленное в боях">Закаленное в боях</option>
                        <option value="Не покрашено">Не покрашено</option>
                    </select>
                    <select class="shop-selector" style="margin-left: 20px;float: right;" id="rarity_all">
                        <option value="">Все раритетности</option>
                        <option value="Базового класса">базового класса</option>
                        <option value="Армейское качество">Армейское качество</option>
                        <option value="Запрещенное">Запрещенное</option>
                        <option value="Засекреченное">Засекреченное</option>
                        <option value="Тайное">Тайное</option>
                        <option value="Ширпотреб">Ширпотреб</option>
                        <option value="Промышленное качество">Промышленное качество</option>
                    </select>
                    <select class="shop-selector" style="margin-left: 20px;float: right;" id="sort_all">
                        <option value="desc">По убыванию</option>
                        <option value="asc">По возрастанию</option>
                    </select>
                </div>
            </div>
            <div id="items-list" class="items-list" style="display: block;overflow: auto;max-height: 464px;"></div>
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
    <script>
        function updateBalance() {
            $.post('/getBalance', function (data) {
                $('.userBalance').text(data);
            });
        }
    </script>
@endsection
