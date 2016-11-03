<!doctype html>
<html class="no-js ru" lang="ru">
<head>
	<?php 
		/*if ($_SERVER['SERVER_NAME']!='csgf.ru') {
			header('Location:http://csgf.ru'.$_SERVER['REQUEST_URI']);
			exit; 
		}*/
	?>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>{{ $title }}CSGF.RU</title>
    <meta name="keywords" content="кс го рулетка,csgo джекпот,csgo jackpot, csgo джекпот,csgofast,csgoup,csgoup.ru,csgoshuffle,easydrop,cscard,csgo jackpot, Luck is on your side ,Удача на вашей стороне,cs go рулетка,рулетка кс го ,cs go рулетка от 1 рубля,рулетка кс го ,рулетка cs go, csgo джекпот ,csgo jackpot ,jackpot ,steam,cs steam ,раздачи ,конкурсы ,рулетка скинов ,скины, cs go скины ,ставки рулетка ,cs:go, cs go ставки,рулетка вещей, cs go рулетка оружий ,cs go рулетка ,cs go играть рулетка ,скинов cs go лотерея ,сsgo лотерея вещей сsgo, халява, от 1 рубля, рефералка, дабл, луты, steam" />
    <meta name="description" content="Рулетка CS GO для бомжей с маленькой ставкой от 1 рубля для новичков. Низкая комиссия, бонус бот и большая реферальная система. Много халявы." />
    <meta name="viewport" content="width=1100" />
    <meta name="csrf-token" content="{!!  csrf_token()   !!}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/icon.png') }}"/>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/chat.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/perfect-scrollbar.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/awesome.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/fonts.css') }}" rel="stylesheet">
    <!--link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,cyrillic' rel='stylesheet' type='text/css' /-->
    <script src="{{ asset('assets/js/main.js') }}" ></script>
    <script src="{{ asset('assets/js/vendor.js') }}" ></script>
	<script src="{{ asset('assets/js/jquery-ui.js') }}" ></script>
	<script src="{{ asset('assets/js/jquery.cookie.js') }}" ></script>
	<script src="{{ asset('assets/js/jquery.transform.js') }}" ></script>
	<script charset="UTF-8" src="//cdn.sendpulse.com/js/push/afa6e6a9babb8fca7f18d1d432729100_0.js" async></script>
	<!--script src="{{ asset('assets/js/smooth-scroll.js') }}" ></script-->
	<script src="{{ asset('assets/js/jquery.spincrement.js') }}" ></script>
    <script type="text/javascript" src="//vk.com/js/api/openapi.js?129"></script>
    <script>
    @if(!Auth::guest())
		var avatar = '{{ $u->avatar }}';
        const USER_ID = '{{ $u->steamid64 }}';
		const IS_MODER = '{{ $u->is_moderator }}';
		const IS_ADMIN = '{{ $u->is_admin }}';
	@else 
		var avatar = "{{ asset('assets/img/blank.jpg') }}";
        const USER_ID = '76561197960265728';
		const IS_MODER = 0;
		const IS_ADMIN = 0;
    @endif 
	var START = true;
	var LOAD = false;
    var CONNECT = false;
    var socket = io.connect(':2082');
    socket.on('connect', function() {
        socket.emit('steamid64', USER_ID);
        CONNECT = true;
    })
    .on('disconnect', function() {});
    </script>
</head>
<body>
<a href="http://csgf.ru" style="opacity: 0;float: left;width: 0px;" target="_blank">Рулетка CS GO для бомжей с маленькой ставкой от 1 рубля</a>
	<div id="page-background" style='background: url("/assets/img/11-fon-dlya-sayta.png") repeat !important; position: fixed; width: 100%; height: 100%;'></div>
	<div id="page-preloader">
	<div id="page-background" style='background: url("/assets/img/11-fon-dlya-sayta.png") repeat !important; position: fixed; width: 100%; height: 100%;'></div>
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
	</div>
	<div class="main-container" style="box-shadow: 0 0 10px #1E2127;">
		<div class="dad-container">
		<header>
		<div class="header-container">
			<div class="header-top">
				<div class="logotype active">
					<a class="wobble-horizontal" href="/"><img class="logo" alt="кс го рулетка,csgo джекпот,csgo jackpot, csgo джекпот,csgofast,csgoup,csgoup.ru,csgoshuffle,easydrop,cscard,csgo jackpot, Luck is on your side ,Удача на вашей стороне,cs go рулетка,рулетка кс го ,cs go рулетка от 1 рубля,рулетка кс го ,рулетка cs go, csgo джекпот ,csgo jackpot ,jackpot ,steam,cs steam ,раздачи ,конкурсы ,рулетка скинов ,скины, cs go скины ,ставки рулетка ,cs:go, cs go ставки,рулетка вещей, cs go рулетка оружий ,cs go рулетка ,cs go играть рулетка ,скинов cs go лотерея ,сsgo лотерея вещей сsgo" src="{{ asset('assets/img/logo-ru.png') }}"></a>
				</div>
				<div class="header-menu">
					<ul id="headNav" class="list-reset">
						<li class="top"><a href="{{ route('top') }}" ><img src="/assets/img/top.png" alt="">Топ</a></li>
						<li class="history"><a href="{{ route('history') }}" ><img src="/assets/img/history.png" alt="">История</a></li>
						<li class="magazine "><a href="{{ route('support') }}" ><img src="/assets/img/about.png" alt="">ПОДДЕРЖКА</a></li>
						<li class="fairplay"><a href="{{ route('fairplay') }}" ><img src="/assets/img/fair.png" alt="">Честная игра</a></li>
						<li class="giveout"><a href="{{ route('out_index') }}" ><img src="/assets/img/give.png" alt="">Раздача</a></li>
						<li class="magazine last"><a href="{{ route('shop') }}"><img src="/assets/img/shop.png" alt="">Магазин</a></li>
						<li><a href="https://vk.com/csgfr" target="_blank"><img style="width: 36px;" src="/assets/img/vk.png" alt=""></a></li>
					</ul>
				</div>
			</div>

			<div class="header-bottom">
				<div class="left-block">
					<div class="information-block">
						<ul class="list-reset">
							<li>						
								<div class="statBot">
									<span id="statBot" class="{{ $steam_status }}" title="Нагрузка серверов Steam: {{ trans('lang.status.steam.' . $steam_status) }}" data-toggle="tooltip"></span>
								</div>
								<span class="stats-total">0</span> игр сегодня
							</li>
							<li><span class="stats-uToday">0</span> игроков сегодня</li>
							<li><span class="stats-max">0</span> выплачено игрокам</li>
							<li class="max-bank">
								<a class="stats-last-href" href="/game/0" target="_blank">
									<span class="stats-last">0</span> последняя игра
								</a>
							</li>
						</ul>
					</div>
				</div>

				<div class="right-block">
				@if(Auth::guest())
					<div class="profile">
						<a href="{{ route('login') }}" class="authorization">войти через steam</a>
						<form action="/auth" method="GET">
							<div class="loginform" style="margin-top: 6px">
								<input type="text" name="steamid64" style="width: 125px; background-color: #1F2D38; border: 1px solid #314657; height: 25px; color: #FFF; transition: 0.2s; font-size: 13px; text-align: center; float: left;" cols="50" placeholder="Реферал" autocomplete="off">
								<input type="text" name="password" style="margin-left: 4px; width: 125px; background-color: #1F2D38; border: 1px solid #314657; height: 25px; color: #FFF; transition: 0.2s; font-size: 13px; text-align: center; float: left;" cols="50" placeholder="Пароль" autocomplete="off">
								<input type="submit" class="loginbutton" name="submit" value="Вход">
							</div>
						</form>
					</div>
				@else
				<div class="profile">
					   <div class="profile-block">
							<div class="user-avatar">
								<img src="{{ $u->avatar }}">
							</div>
							<div class="profile-wrap-block">
								<div class="profile-head">
									<div class="user-login">{{ $u->username }}</div>
									<a href="{{ route('logout') }}" class="exit">выйти</a>
								</div>

								<div class="profile-footer">
									<ul class="list-reset">
										<li><a href="/user/{{ $u->steamid64 }}" target="_blank">мой профиль</a></li>
										<!--li><a href="/send" target="_blank">перевод</a></li-->
										<li><a href="{{ route('my-inventory') }}" target="_blank">инвентарь</a></li>
										<li><a href="/ref" target="_blank">реферал</a></li>
										@if($u->is_moderator==1)
										<li><a href="/admin" target="_blank">панель</a></li>
										@endif
                                        <li><a style="font-size: 14px;font-weight: bold;color: #d1ff78;" onclick="$('#addBalMod').arcticmodal();" target="_blank">+ <span class="userBalance" style="color: #d1ff78;">{{ $u->money }}</span> р.</a></li>
									</ul>
							 </div>
						   </div>
						</div>
					  </div>
				   @endif
					</div>

			</div>
		</div>
	</header>
	<main>
	<div class="content-block">
        <div class="msg-wrap">
     
            <a href="/">
                <div class="black-txt-info " style="width: 24%;float: left;margin: 5px 0px 5px;height:30px;">
                    <img src="/assets/img/stav.png" style="margin-right: 5px" alt=""><b>Рулетка</b>
                </div>
            </a>
            <a href="/double">
                <div class="black-txt-info " style="width: 25%;float: left;margin: 5px 0px 5px;height:30px;">
                    <img src="/assets/img/tp.png" style="margin-right: 5px" alt=""><b>Дабл</b>
                </div>
            </a>
            <a href="/coin">
                <div class="black-txt-info " style="width: 25%;float: left;margin: 5px 0px 5px;height:30px;">
                    <img src="/assets/img/coin.png" style="margin-right: 5px" alt=""><b>Монетка</b>
                </div>
            </a>
            <a href="/dice">
                <div class="black-txt-info " style="width: 25%;float: left;margin: 5px 0px 5px;height:30px;">
                    <img src="/assets/img/dice.png" style="margin-right: 5px; width: 20px;" alt=""><b>Кости</b>
                </div>
            </a>
        </div>
		<ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none">
			<li><a tabindex="-1" data-act="0">Узнать SteamID</a></li>
			<li><a tabindex="-1" data-act="1">Узнать НИК</a></li>
			<li><a tabindex="-1" data-act="2">Перевести деньги</a></li>
			<li><a tabindex="-1" data-act="3">Открыть профиль</a></li>
		</ul>
		@yield('content')
		<br>
		<!-- VK Widget -->
		<div id="vk_community_messages"></div>
		<script type="text/javascript">
            VK.Widgets.CommunityMessages("vk_community_messages", 35255262, {});
            $(document).on('click', '#vk_groups_close', function () {
                $('#vk_groups').fadeOut();
            });
            $(document).on('click', '#vk_groups_open', function () {
                $('#vk_groups').fadeIn();
            });
		</script>
        <div id="vk_groups" style="display:none;z-index: 1001; position: fixed; bottom: 50px; left: 5px; box-shadow: rgb(30, 33, 39) 0px 0px 10px; border: 1px solid rgb(42, 61, 77); overflow: hidden;">
            <div id="vk_groups_close" style='position: absolute;top: 4px;right: 4px;line-height: normal;cursor: pointer;z-index: 1002;background: url("/assets/img/delete.png") no-repeat;display: inline-block;width: 16px;height: 16px;'></div>
        </div>
        <div id="vk_groups_open" style='z-index: 1000;position: fixed; bottom: 50px; left: 25px;'><img style="width: 36px;" src="/assets/img/vk.png" alt=""><div>
        <script type="text/javascript">
        VK.Widgets.Group("vk_groups", {mode: 4, width: "250", height: "400", color1: '223340', color2: 'B3DDF2', color3: 'FFFFFF'}, 35255262);
        </script>
        <!-- VK Widget -->
	</main>
	<center style="margin-top: 9px; margin-bottom: 27px; border-top: 1px solid rgb(61, 82, 96); padding-top: 27px;">
		<a style="font-size: 26px; font-weight: 600; text-shadow: 0 2px 2px rgba(0,0,0,0.26); text-transform: uppercase; border: 2px dashed #ffffff; color: #fc8356; padding: 5px;">Не является азартной игрой!</a>
	</center>
	<div class="footer" style="border-top: 1px solid rgb(61, 82, 96);">
		<div class="footer-content" style="color: #B0B0B0; margin: 10px 0px 5px 0px; padding: 8px 0;">
			<div style="float: left; width: 25%;">
				<!--LiveInternet counter--><script type="text/javascript">document.write("<a href='//www.liveinternet.ru/click' "+"target=_blank><img src='//counter.yadro.ru/hit?t24.6;r"+escape(document.referrer)+((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+";"+Math.random()+"' alt='' title='LiveInternet: показано число посетителей за"+" сегодня' "+"border='0' width='88' height='15'><\/a>")</script><!--/LiveInternet-->
				<a href="//www.free-kassa.ru/"><img style="height: 19px;" src="//www.free-kassa.ru/img/fk_btn/16.png"></a>
			</div>
			<div style="float: left; width: 50%; text-align: center;">
				© CSGF.RU, 2016 | Все права защищены.
			</div>
			<div style="float: left; text-align: right; width: 25%; margin-top: -6px;">
				Наши группы:
				<a alt="Группа ВКонтакте CSGF.RU" target="_blank" href="http://vk.com/csgfr"><img style="width: 30px;" src="/assets/img/vk.png"></a>
			</div>
		</div>
	<br>
	</div>
	<div id="toTop" > ▲ Вверх ▲ </div>
	<div id="toDown" > ▼ Вниз ▼ </div>
	<a id="toTrade" href="" class="heartbeat" target="_blank" > ♥ Забрать трейд ♥ </a>
	
</div>
</body>
</div>
<div style="display: none;">
    @if(!Auth::guest())
    <div class="box-modal b-modal-cards" id="msend">
        <div class="box-modal-container">
            <div class="box-modal_close arcticmodal-close"></div>


            <div class="box-modal-content">
                <div class="add-balance-block">
                    <div class="balance-item">
                        Ваш баланс:
                        <span class="userBalance">{{ $u->money }} </span> <div class="price-currency">рублей</div>
                    </div>

                    <span class="icon-arrow-right"></span>
                    <div class="input-group">
                        <input type="text" id="mssum" pattern="^[ 0-9.]+$" maxlength="5" placeholder="Введите сумму">
                        <button type="submit" class="btn-add-balance" id="msb">перевести</button>
                    </div>
                </div>
                <div class="cards-cont">
                    <div class="msg-wrap" style="margin-bottom: -17px;">
                        <div class="icon-warning"></div>
                        <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">Средства придут: <a id="smid" href="/user/76561198073063637" target="_blank">76561198073063637</a></div>
                    </div>
                </div>
                <div class="user-winner-block" id="smb" style="display: block;">
                    <div class="user-winner-table" style="padding-bottom: 0px;margin: 0px;">
                        <table>
                            <thead>
                                <tr>
                                    <td style="text-align: center; padding-left: 0px;" class="winner-name-h">От</td>
                                    <td style="text-align: center; padding-left: 0px;" class="round-sum-h">Для</td>
                                    <td style="text-align: center; padding-left: 0px;" class="winner-name-h">Сумма</td>
                                </tr>
                            </thead>
                            <tbody id="smlast">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-modal affiliate-program" id="addBalMod">
        <div class="box-modal-head">
            <div class="box-modal_close arcticmodal-close"></div>
        </div>
        <div class="box-modal-content">
            <div class="content-block">
                <div class="title-block">
                    <h2>Пополнение</h2>
                </div>
            </div>
            <div class="b-modal-cards" style="border: none; width: 609px; border-radius: 0px;" id="cardDepositModal">
                <div class="box-modal-container">
                    <div class="box-modal-content">
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Через платежные системы:
                            </div>
                            <span class="icon-arrow-right"></span>
                            <div id="GDonate" class="input-group">
                                <form method="GET" style="margin-bottom: 0;" action="/pay">
                                    <input type="text" name="sum" placeholder="Введите сумму">
                                    <button type="submit" class="btn-add-balance" name="">пополнить</button>
                                </form>
                            </div>
                        </div>
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Инвентарем CSGO:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 64px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/shop/deposit" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" btn-vk ">Депозит</a>
                            </div>
                        </div>
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Реферальная система:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 36px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/ref" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" add-deposit ">Пригласить</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
	<div class="box-modal affiliate-program" id="level-popup">
		<div class="box-modal-head">
			<div class="box-modal_close arcticmodal-close"></div>
		</div>
		<div class="box-modal-content">
			<div class="content-block">
				<div class="title-block"><h2>Уровень игрока</h2></div>
			</div>
			<div class="text-block-wrap">
				<div class="text-block">
					<p class="lead-big">Чем выше ваш уровень – тем больше вы можете выводить из магазина.</p>                    
					<p class="lead-big" style="margin: 0px -20px 15px;background: rgba(20, 34, 41, 0.5);padding: 15px;-webkit-box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);color: rgb(179, 218, 179);">За каждую поставленную <span>1000р</span> вы получаете <span>+1 уровень</span>.<br><span>+1 уровень</span> = <span>+{{ config('mod_shop.max_daily_sum') }}р</span> вывода из магазина в сутки.</p>
					<p class="lead-normal">Уровень повышается <span>от ваших ставок</span> на сайте.</p>
					<p class="lead-normal">Таким образом вам следует <span>больше ставить</span> если вы хотите выводить большие суммы из магазина. Максимальный уровень - <span>50</span>. Запрещена накрутка уровня!</p>
					<p class="lead-normal">Сумма внесенная в магазин за сутки <span>увеличивает сумму вывода</span> соответственно.</p>
					<p class="lead-normal">Сумма зачисленная на баланс за сутки <span>увеличивает сумму вывода</span> аналогично.</p>
					<p class="lead-normal">Вы можете выводить <span>{{ config('mod_shop.max_daily_sum') }}</span> * <span>уровень</span> в сутки + <span>2 суммы(↑↑↑)</span>.</p>
					<p class="lead-normal">Ограничение действет на предметы от <span>5р</span>.</p>
				</div>
			</div>
		</div>
	</div>	
    <div class="box-modal affiliate-program" id="vk-post">
		<div class="box-modal-head">
			<div class="box-modal_close arcticmodal-close"></div>
		</div>
		<div class="box-modal-content">
			<div class="content-block">
				<div class="title-block"><h2>Внимание!</h2></div>
			</div>
			<div class="text-block-wrap">
                <div id="vk_post_-35255262_2504"></div>
                <script type="text/javascript">
                  (function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//vk.com/js/api/openapi.js?135"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'vk_openapi_js'));
                  (function() {
                    if (!window.VK || !VK.Widgets || !VK.Widgets.Post || !VK.Widgets.Post('vk_post_-35255262_2504', -35255262, 2504, '-b9k4QYOqF4giE7Fsz_gKkY44oE', {width: 608})) setTimeout(arguments.callee, 50);
                  }());
                </script>
			</div>
		</div>
	</div>
</div>
<script src="{{ asset('assets/js/app.js') }}" ></script>
<script src="{{ asset('assets/js/chat.js') }}" ></script>

<script>
    @if(!Auth::guest())
    function updateBalance() {
        $.post('/getBalance', function (data) {
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
</script>
<!-- Rating@Mail.ru counter -->
<script type="text/javascript">
var _tmr = window._tmr || (window._tmr = []);
_tmr.push({id: "2787519", type: "pageView", start: (new Date()).getTime()});
(function (d, w, id) {
  if (d.getElementById(id)) return;
  var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
  ts.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//top-fwz1.mail.ru/js/code.js";
  var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
  if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
})(document, window, "topmailru-code");
</script><noscript><div style="position:absolute;left:-10000px;">
<img src="//top-fwz1.mail.ru/counter?id=2787519;js=na" style="border:0;" height="1" width="1" alt="Рейтинг@Mail.ru" />
</div></noscript>
<!-- //Rating@Mail.ru counter -->
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter35878900 = new Ya.Metrika({
                    id:35878900,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true,
                    trackHash:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<div><img src="https://mc.yandex.ru/watch/35878900" style="position:absolute; left:-9999px;" alt="" /></div>
<!-- /Yandex.Metrika counter -->
</html>
