@extends('layout')

@section('content')
    <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
    <div class="title-block">
        <h2>
            Депозит
        </h2>
    </div>
    <div class="page-content">
        <div class="page-main-block" style="border-bottom: 1px solid #3D5260; width:60%; display: inline-block;padding-top: 20px;border-right: 1px solid #3D5260;margin-bottom:0px">
            <div class="page-mini-title">Важная информация при депозите:</div>
            <div class="page-block">
                <ul>
                    <li>Предметы стоимостью <b>менее 10р</b> оцениваются в <b>2 раза</b> дешевле.</li>
                    <li>Предметы стоимостью <b>менее {{ config("mod_shop.dep_comission_from") }}р</b> оцениваются дешевле на <b>{{ config('mod_shop.dep_comission_%') }}%</b>.</li>
                    <li>Цену предмета вы видите сразу при выборе.</li>
                    <li>Ваш лимит на сутки будет увеличен на сумму пополнения.</li>
                </ul>
            </div>
        </div>
        <div class="page-main-block" style="border-bottom: 1px solid #3D5260; width:39%; display: inline-block;padding-top: 20px;padding-left:15px;margin-bottom:0px">
            <div class="page-mini-title">Как пополнять:</div>
            <div class="page-block">
                <ul>
                    <li>Вы нажимаете "Обновить инвентарь".</li>
                    <li>Выбираете предметы для депозита.</li>
                    <li>Нажимаете "Внести предметы".</li>
                    <li>Принимаете обмен в стиме".</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="buy-cards-container" style="padding-top: 10px;">        
        <div style="overflow: hidden;">
            <div class="buy-cards-block" style="margin-top: 0px;">
                <div class="shop-item-filters">
                    <div class="left-totalitems">
                       Ваши выбранные предметы | Предметов: <span id="cart-total">0</span> | Сумма: <span id="cart-total-price">0</span>
                    </div>
                    <a href="{{ route('cards-history') }}" class="btn-history">История обменов</a>
                    <a class="btn-buy" id="get-cart">Внести предметы</a>
                </div>
            </div>
            <div id="cart-list" class="cart-list" style="margin-right: -25px;display: block;overflow: auto;max-height: 464px;"></div>
            <div class="buy-cards-block" style="margin-top: 0px;">
                <div class="shop-item-filters" style="margin-bottom:0;">
                    <div class="left-totalitems">
                        Найдено предметов: <span id="filter-total">0</span> / <span id="items-total">0</span>
                    </div>
                    <a class="btn-inv" target="_blank">&#8634;</a>
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
                        Выберите вещи для продажи
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
                        <option value="Базового класса">Базового класса</option>
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
    <div style="display: none;">
        <div class="box-modal affiliate-program" id="depModal">
            <div class="box-modal-head">
                <div class="box-modal_close arcticmodal-close"></div>
            </div>
            <div class="box-modal-content">
                <div class="content-block">
                    <div class="title-block">
                        <h2>Обмен отправлен</h2>
                    </div>
                </div>
                <div class="b-modal-cards" style="border: none; width: 609px; border-radius: 0px;" id="cardDepositModal">
                    <div class="box-modal-container">
                        <div class="box-modal-content">
                            <div class="add-balance-block">
                                <div class="balance-item">
                                    Код безопасности:
                                </div>
                                <span class="icon-arrow-right"></span>
                                <div class="balance-item">
                                    <span id="depTradeCode">XXXXX</span>
                                </div>
                                <span class="icon-arrow-right"></span>
                                <a class="btn-buy" href="" target="_blank" style="float: none;" id="depUrl">Принять обмен</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateBalance() {
            $.post('/getBalance', function (data) {
                $('.userBalance').text(data);
            });
        }
    </script>
@endsection
