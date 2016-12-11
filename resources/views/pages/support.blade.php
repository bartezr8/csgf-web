@extends('layout')
@section('content')
    <div class="faq-page">
		<div class="escrow-banner">
			<div style="display: inline-block;"><b>ВНИМАНИЕ!</b> ВАЖНЫЕ ИЗМЕНЕНИЯ В <b>STEAM</b>: ВСЕМ ПОЛЬЗОВАТЕЛЯМ НЕОБХОДИМО УСТАНОВИТЬ МОБИЛЬНЫЙ АУТЕНТИФИКАТОР!!!</div>
			<a href="/escrow" target="_blank">ЧИТАТЬ ПОДРОБНЕЕ</a>
		</div>

        <div class="title-block">
            <h2>
                Поддержка
            </h2>
        </div>

        <div class="page-content">

            <div class="page-main-block">
                <div class="page-mini-title">Что это такое?</div>
                <div class="page-block">
                    CSGF.RU – сервис, в котором участвующие вносят свои скины CS:GO или виртуальные карточки регулируемой стоимости, и как только в раунде набирается 100 предметов или проходит 2 минуты с момента начала игры, выбирается один победитель с помощью системы честной игры, которому достаются все внесенные предметы в раунде, не считая комиссии.
                </div>
            </div>

            <div class="page-main-block" style="border-bottom: 1px solid #3D5260; padding-bottom: 20px;">
                <div class="page-mini-title">Как это работает:</div>
                <div class="page-block">
                    <ul>
                        <li>Вы вносите свои предметы через кнопку «Принять участие», отправляя трейд одному из наших ботов.<br>
                            <span>Вы можете внести максимум {{ config('mod_game.max_items') }} предметов за игру, но {{ config('mod_game.max_items_per_trade') }} предметов за обмен, общая сумма которых должна быть не менее {{ config('mod_game.min_price') }} рублей.</span>
                        </li>

                        <li>За каждый внесенный 1 рубль вы получите 100 билетов (1 копейка - 1 билет)<br>
                            <span>Шанс на победу напрямую зависит только от количества ваших билетов за игру. Следовательно, чем на большую сумму депозит вы внесете, тем выше будет ваш шанс на победу.</span>
                        </li>

                        <li>Когда наберется 100 скинов или пройдет 2 минуты с момента начала игры, будет выбран победный билет<br> с помощью системы <a href="/fairplay" target="_blank">Честной игры</a>. Победителем окажется тот игрок, у которому будет принадлежать данный билет.</li>

                        <li>Победитель получает все внесенные предметы в раунде (не учитывая нашу комиссию от 0 до 10%).<br>
                            <span>Предметы из CSGO будут отправлены победителю нашими ботами в стиме, карточки будут зачислены на баланс.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Мне пришли не все предметы после победы!</div>
                <div class="page-block">
                    С каждой игры мы берем комиссию от 5% до 10% в зависимости от банка, наличия ссылки на сайт в нике игрока и номера ставки.<br>
                    Предметы из CSGO могут приходить в разных трейдах. Предметы могут не отправиться с первого раза.<br>
                    Если хоть один трейд не отправиться - в итории стаус отправки будет "Ошибка отправки".<br>
                    Все игры со статусом "Ошибка отправки" переотправляются каждые 15 минут.
                    
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Мне не пришел выигрыш!</div>
                <div class="page-block">
                    Откройте <a href="/history" target="_blank">Иторию игр</a> и посмотрите на стаус своей игры. Если статус отправки "Отправка выигрыша" просто немного подождите.<br>
                    Все игры со статусом "Ошибка отправки" переотправляются каждые 15 минут. Если через 15 минут обмен все равно не приходит - проверьте актуальность ваших данных:<br>
                    <span>В <a href="http://steamcommunity.com/id/me/edit/settings/" target="_blank">НАСТРОЙКАХ ПРИВАТНОСТИ</a> вашего аккаунта Steam ваш инвентарь должен быть открыт!</span><br>
                    <span>Обязательно укажите ЗАНОГО ссылку на обмен. большинство ошибок с отправкой трейда происходит из за устаревшей ссылки. Если не помогло - пищите в поддежку.</span>
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Я внес депозит, но предметы не вошли в игру.</div>
                <div class="page-block">
                    Такое бывает крайне редко и происходит это исключительно из-за проблем со стимом. В таком случае вам нужно будет написать в поддержку, и вам повторят обработку трейда.
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Ваш бот отклоняет мой трейд!</div>
                <div class="page-block">
                    Когда ваш трейд отклонился - на сайте вы должны были увидеть ошибку с причиной отклонения,<br>
                    это может быть одна из следующий причин:<br>
                    - минимальная сумма депозита {{ config('mod_game.min_price') }} рублей;<br>
                    - максимально предметов за обмен - {{ config('mod_game.max_items_per_trade') }}<br>
                    - принимаются предметы только с CS:GO;<br>
					- если вы забанены на сайте бот примет вещи но не сделает ставку;
                </div>
            </div>
            <div class="page-main-block">
                <div class="page-mini-title">Правила обращения в поддержку!</div>
                <div class="page-block">
                    Обязательные правила для наиболее быстрого решения проблем:<br>
                    - начинайте свое обращение с темы, если не пришел обмен так и пишите!<br>
                    - указывайте максимально возможное колличество информации и пишите в одно сообщение;<br>
                    - пишите на русскои или английском языке, мы не рассматриваем обращения на других;<br>
                    - не пытайтесь оскорбить или обвинить в мошенничестве администраторов сервиса;<br>
                    - не пытайтесь угрожать или манипулировать администраторами сообщества;<br>
                    - не пытайтесь добиться подкрутки или информации о победителе;<br>
                    - если у вас проблемы с получением вещей, но вы не указали номер игры - бан в поддежке<br>
                </div>
            </div>

            <div class="faq-block faq-last">
                <div class="faq-text">
                    В подержку можно обратиться на всех страницах сайта. Окно связи с оператором находиться справа внизу. <br>
                    Время ответа на ваш запрос до 24 часов.
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