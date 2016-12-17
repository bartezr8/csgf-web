<header id="header">
    <div class="header-container">
        <div class="header-top">
            <div class="logotype active">
                <a href="/"><img class="logo" alt="кс го рулетка,csgo джекпот,csgo jackpot, csgo джекпот,csgofast,csgoup,csgoup.ru,csgoshuffle,easydrop,cscard,csgo jackpot, Luck is on your side ,Удача на вашей стороне,cs go рулетка,рулетка кс го ,cs go рулетка от 1 рубля,рулетка кс го ,рулетка cs go, csgo джекпот ,csgo jackpot ,jackpot ,steam,cs steam ,раздачи ,конкурсы ,рулетка скинов ,скины, cs go скины ,ставки рулетка ,cs:go, cs go ставки,рулетка вещей, cs go рулетка оружий ,cs go рулетка ,cs go играть рулетка ,скинов cs go лотерея ,сsgo лотерея вещей сsgo" 
                src="{{ $asset('/assets/img/' . config('app.logo')) }}" style="margin-top: -12px;"></a>
            </div>
            <div class="header-menu">
                <ul id="headNav" class="list-reset">
                    <li class="top"><a href="{{ route('top') }}" ><img src="/assets/img/top.png" alt="">Топ</a></li>
                    <li class="history"><a href="{{ route('history') }}" ><img src="/assets/img/history.png" alt="">История</a></li>
                    <li class="magazine "><a href="{{ route('support') }}" ><img src="/assets/img/about.png" alt="">ПОДДЕРЖКА</a></li>
                    <li class="fairplay"><a href="{{ route('fairplay') }}" ><img src="/assets/img/fair.png" alt="">Честная игра</a></li>
                    <li class="giveout"><a href="{{ route('out_index') }}" ><img src="/assets/img/give.png" alt="">Раздача</a></li>
                    <li class="magazine last"><a href="{{ route('shop') }}"><img src="/assets/img/shop.png" alt="">Магазин</a></li>
                    <li><a href="https://vk.com/csgfru" target="_blank"><img style="width: 36px;" src="/assets/img/vk.png" alt=""></a></li>
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
            <div class="right-block" style="height: 65px;">
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
                                    <li><a href="{{ route('my-inventory') }}" target="_blank">инвентарь</a></li>
                                    <li><a href="/ref" target="_blank">реферал</a></li>
                                    @if($u->is_moderator==1)
                                    <li><a href="/admin" target="_blank">панель</a></li>
                                    @endif
                                    <li class="profile-balance heartbeat"><a style="font-size: 14px;font-weight: bold;color: #d1ff78;" onclick="$('#addBalMod').arcticmodal();" target="_blank">+ <span class="userBalance orbit" style="color: #d1ff78;">{{ $u->money }}</span> р.</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
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
</header>