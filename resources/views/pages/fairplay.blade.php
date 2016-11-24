@extends('layout')

@section('content')
    <div class="content-block">
        <script src="{{ $asset('assets/js/md5.min.js') }}"></script>
        <script src="{{ $asset('assets/js/check.js') }}"></script>
        <div class="honest-page">

            <div class="title-block">
                <h2>Честная игра</h2>
            </div>

            <div class="honest-container">
                <div class="honest-content">

                    <div class="honest-main-block">
                        <div class="honest-title">Как это работает</div>
                        <div class="honest-block">
                            Наша система честной игры работает таким образом, что победитель определяется с помощью <b>Числа раунда</b>, которое случайным образом генерируется в начале игры.<br>
                            <b>Число раунда</b> зашифровывается с помощью MD5, и этот хэш показывается в начале каждого раунда.<br>
                            В конце раунда система показывает то самое расшифрованное <b>Число раунда</b>, которое были зашифрованно в самом начале, и вы сможете проверить, что <b>Число раунда</b> не менялось на протяжении игры.<br>
                            Число раунда умножается на общее количество билетов в раунде и таким образом выбирается победный билет. У кого из игроков будет данный победный билет, тот и окажется победителем.
                            <br><br>
                            То есть принцип честной игры работает таким образом, что мы никак не можем знать сколько билетов будет на момент завершения раунда, а <b>Число раунда</b> для умножения дается в самом начале раунда.
                        </div>
                    </div>

                    <div class="honest-main-block">
                        <div class="honest-title">Обозначения</div>
                        <div class="honest-mini-title">Число раунда</div>
                        <div class="honest-block">Случайное дробное число от 0 до 1 (например: 0.8612523461234567)</div>

                        <div class="honest-mini-title">Хэш</div>
                        <div class="honest-block">MD5 хэш шифруется строка: <span>число_раунда</span>, используется чтобы доказать честность игры.</div>

                        <div class="honest-mini-title">Билет</div>
                        <div class="honest-block">За каждую внесенную 1 коп. вы получите 1 билет.</div>
                    </div>

                    <div class="honest-main-block">
                        <div class="honest-title">Выбор победителя</div>
                        <div class="honest-block">
                            Каждый депозит переводится в билеты. Билеты сортируются по времени депозита.
                            <br><br>
                            Номер победного билета считается по следующей формуле:
                            <span>ceil(число билетов * число раунда) = победитель</span><br>
                            (функция floor возвращает ближайшее целое число, округляя переданное ей число в меньшую сторону).
                            <br><br>
                            Игрок, у которого будет выбранный победный билет и окажется победителем в раунде.
                        </div>
                    </div>

                    <div class="honest-main-block">
                        <div class="honest-title">
                            Проверка                 </div>
                        <div class="honest-block">
                            Вы можете использовать этот инструмент, чтобы убедиться, что вас не обманывают, и вычислить номер победного билета.
                        </div>
                    </div>

                    <div style="height: 210px;" id="check">
                        <div class="honest-form">
                            <input id="totalbank" style="width: 160px;" type="text" value="@if(!empty($bet)) {{ $bet->to }} @endif" placeholder="Банк в копейках">
                            <input id="roundRandom" style="width: 170px;" type="text" value="@if(!empty($game)) {{ $game->rand_number }}  @endif" placeholder="Число раунда">
                            <input id="roundHash" style="width: 280px;" type="text" value="@if(!empty($game)) {{ md5($game->rand_number) }} @endif" placeholder="Хэш">
                        </div>

                        <div id="checkHash" class="honest-check">Проверить</div>

                        <div id="checkResult" class="honest-check-text">@if(!empty($game)) Хэш соответствует числу раунда и секрету. Победный билет: {{ ceil($game->rand_number * $bet->to) }} @else &nbsp; @endif</div>
                    </div>
                    
                </div>
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
                        <?php for($i = 1; $i <= 505; $i++)echo "<img src=\"/assets/img/smiles/smile (".$i.").png\" id=\"smile\" style=\"background:none;\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
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