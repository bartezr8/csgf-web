<!doctype html>
<html class="no-js ru" lang="ru">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>{{ $title }}CSGF.RU</title>
        <meta name="keywords" content="кс го рулетка,csgo джекпот,csgo jackpot, csgo джекпот,csgofast,csgoup,csgoup.ru,csgoshuffle,easydrop,cscard,csgo jackpot, Luck is on your side ,Удача на вашей стороне,cs go рулетка,рулетка кс го ,cs go рулетка от 1 рубля,рулетка кс го ,рулетка cs go, csgo джекпот ,csgo jackpot ,jackpot ,steam,cs steam ,раздачи ,конкурсы ,рулетка скинов ,скины, cs go скины ,ставки рулетка ,cs:go, cs go ставки,рулетка вещей, cs go рулетка оружий ,cs go рулетка ,cs go играть рулетка ,скинов cs go лотерея ,сsgo лотерея вещей сsgo, халява, от 1 рубля, рефералка, дабл, луты, steam" />
        <meta name="description" content="Рулетка CS GO для бомжей с маленькой ставкой от 1 рубля для новичков. Низкая комиссия, бонус бот и большая реферальная система. Много халявы." />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" type="image/png" href="{{ $asset('favicon.png') }}"/>
        <link href="{{ $asset('assets/css/style.css') }}" rel="stylesheet">
        <link href="{{ $asset('assets/css/chat.css') }}" rel="stylesheet">
        <link href="{{ $asset('assets/css/smiles.css') }}" rel="stylesheet">
        <link href="{{ $asset('assets/css/perfect-scrollbar.css') }}" rel="stylesheet">
        <link href="{{ $asset('assets/css/awesome.css') }}" rel="stylesheet">
        <link href="{{ $asset('assets/css/fonts.css') }}" rel="stylesheet">
        <script src="{{ $asset('assets/js/main.js') }}" ></script>
        <script src="{{ $asset('assets/js/vendor.js') }}" ></script>
        <script src="{{ $asset('assets/js/centrifuge.js') }}" ></script>
        <script src="{{ $asset('assets/js/jquery-ui.js') }}" ></script>
        <script src="{{ $asset('assets/js/jquery.cookie.js') }}" ></script>
        <script src="{{ $asset('assets/js/jquery.transform.js') }}" ></script>
        <script>
            var USER_ID = '{{ $u->steamid64 }}',
                SM_ID = '{{ $u->steamid64 }}',
                MAX_ITEMS = "{{ config('mod_game.game_items') }}",
                IS_MODER = '{{ $u->is_moderator }}',
                IS_ADMIN = '{{ $u->is_admin }}';
                USER_BALANCE = {{ $u->money }},
                CENT_TIKEN = "{{ $ctoken }}",
                CENT_TIME = "{{ $ctime }}",
                LOAD = false;
        </script>
    </head>
    <body> 
        <a href="{{ config('app.url') }}" style="opacity: 0;float: left;width: 0px;" target="_blank">Рулетка CS GO для бомжей с маленькой ставкой от 1 рубля</a>
        <div id="page-background" style='background: url("/assets/img/background.png") repeat !important; position: fixed; width: 100%; height: 100%;'></div>
        <div id="page-preloader">
            <div id="page-background" style='background: url("/assets/img/background.png") repeat !important; position: fixed; width: 100%; height: 100%;'></div>
            <div class="cssload-thecube">
                <div class="cssload-cube cssload-c1"></div>
                <div class="cssload-cube cssload-c2"></div>
                <div class="cssload-cube cssload-c4"></div>
                <div class="cssload-cube cssload-c3"></div>
            </div>
        </div>
        <div class="language_switcher">
            <a style="" title="Отключить звук" class="sound_off wobble-horizontal"><div class="sound"></div></a>
            <a style="display: none;" title="Включить звук" class="sound_on wobble-horizontal"><div class="sound-off"></div></a>
            <a style="" title="Отключить снег" class="snow_off wobble-horizontal"><div class="snow"></div></a>
            <a style="display: none;" title="Включить снег" class="snow_on wobble-horizontal"><div class="snow-off"></div></a>
        </div>
        <div class="main-container">
            @include('includes.header')
            <div class="dad-container">
                <main>
                    <div class="content-block">
                        <div id="marg" style="padding-top: 183px;"></div>
                        <ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none">
                            <li><a tabindex="-1" data-act="0">Копировать SteamID</a></li>
                            <li><a tabindex="-1" data-act="1">Копировать НИК</a></li>
                            <li><a tabindex="-1" data-act="2">Перевести средства</a></li>
                            <li><a tabindex="-1" data-act="3">Профиль Steam</a></li>
                            <li><a tabindex="-1" data-act="4">Профиль CSGF.RU</a></li>
                            @if(!Auth::guest())
                                @if($u->is_moderator==1)
                                <li><a tabindex="-1" data-act="5">Управление</a></li>
                                @endif
                            @endif
                        </ul>
                        @yield('content')
                        @include('includes.chat')
                        <br>
                        <script type="text/javascript" src="//vk.com/js/api/openapi.js?129"></script><div id="vk_community_messages"></div>
                    </div>
                </main>
                <center style="margin-top: 9px; margin-bottom: 27px; border-top: 1px solid rgb(61, 82, 96); padding-top: 27px;">
                    <a style="font-size: 26px; font-weight: 600; text-shadow: 0 2px 2px rgba(0,0,0,0.26); text-transform: uppercase; border: 2px dashed #ffffff; color: #fc8356; padding: 5px;">Не является азартной игрой!</a>
                </center>
                @include('includes.footer')
                <div id="toTop" > ▲ Вверх ▲ </div>
                <div id="toDown" > ▼ Вниз ▼ </div>
                <a id="toTrade" href="" class="heartbeat" target="_blank" > ♥ Забрать трейд ♥ </a>
            </div>
        </div>
    </body>
    @include('includes.modals')
    <script src="{{ $asset('assets/js/snowstorm.js') }}"></script>
    <script src="{{ $asset('assets/js/app.js') }}"></script>
    <!--script src="{{ $asset('assets/js/chat.js') }}"></script-->
    <script>
        @if(!Auth::guest())
            function updateBalance() {
                $.post('/getBalance', function (data) {
                    USER_BALANCE = data;
                    $('.userBalance').text(data);
                });
            }
            function addTicket(id, btn){
                $.post('{{route('add.ticket')}}',{id:id}, function(data){
                    updateBalance();
                    return $.notify(data.text, data.type);
                });
            }
        @endif
        function updateSlimit() {
            $.post('/getSlimit', function (data) {
                $('#slimit').text(data);
            });
        }
    </script>
    @include('includes.raiting')
</html>
