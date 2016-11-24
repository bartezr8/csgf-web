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
                    CSGF.RU – сервис, в котором участвующие вносят свои скины CS:GO, и как только в раунде набирается 100 скинов или проходит 2 минуты с момента второго депозита (Не считая бонус бота), система выбирает одного победителя, которому достаются все внесенные скины в раунде.
                </div>
            </div>

            <div class="page-main-block" style="border-bottom: 1px solid #3D5260; padding-bottom: 20px;">
                <div class="page-mini-title">Как это работает:</div>
                <div class="page-block">
                    <ul>
                        <li>Вы вносите свои предметы через кнопку «Принять участие», отправляя трейд нашему боту.<br>
                            <span>Вы можете внести максимум {{ config('mod_game.max_items') }} предметов за раз, общая сумма которых должна быть не менее {{ config('mod_game.min_price') }} рублей.</span></li>

                        <li>За каждый внесенный 1 рубль вы получите 100 билетов (1 копейка - 1 билет)<br>
                            <span>Шанс на победу зависит от количества билетов. Следовательно, чем на большую сумму депозит вы внесете, тем выше будет ваш шанс на победу.</span></li>

                        <li>Когда наберется 100 скинов или пройдет 2 минуты с момента второго депозита, мы выберем победный билет<br> с помощью нашей системы <a href="/fairplay" target="_blank">Честной игры</a>. Победителем окажется тот игрок, у которого будет данный билет.</li>

                        <li>Победитель получает все внесенные предметы в раунде (учитывая нашу комиссию от 5 до 10%) спустя 1 минуту после окончания раунда.</li>
                    </ul>
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Мне пришли не все предметы после победы!</div>
                <div class="page-block">
                    С каждой игры мы берем комиссию от 5% до 10% в зависимости от банка, наличия ссылки на сайт в нике игрока и номера ставки.
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Мне не пришел выигрыш!</div>
                <div class="page-block">
                    В <a href="http://steamcommunity.com/id/me/edit/settings/" target="_blank">НАСТРОЙКАХ ПРИВАТНОСТИ</a> вашего аккаунта Steam ваш инвентарь должен быть открыт! <br>
                    Не поленитесь и укажите заного ссылку на обмен. 80% все ошибок с отправкой трейда происходит из за устаревшей ссылки. <br>
					Обратите внимание что ВСЕ игры со статусом "ОШИБКА ОТПРАВКИ" автоматически переотправляются каждые 15 минут, соответственно в поддержку нужно обращаться только при ошибках обмена где статус отправки "Отправлено"
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Я внес депозит, но предметы не вошли в игру.</div>
                <div class="page-block">
                    Такое бывает крайне редко и происходит это исключительно из-за проблем со стимом. В таком случае вам нужно будет написать нашему саппорту в VK и он повторит обработку трейда.
                </div>
            </div>

            <div class="page-main-block">
                <div class="page-mini-title">Ваш бот отклоняет мой трейд!</div>
                <div class="page-block">
                    Когда ваш трейд отклонился - на сайте вы должны были увидеть ошибку с причиной отклонения,<br>
                    это может быть одна из следующий причин:<br>
                    - минимальная сумма депозита {{ config('mod_game.min_price') }} рублей;<br>
                    - принимаются предметы только с CS:GO;<br>
					- если вы забанены на сайте бот примет вещи но не сделает ставку;
                </div>
            </div>

            <div class="faq-block faq-last">
                <div class="faq-text">
                    Если вы не нашли здесь ответа на ваш вопрос, тогда вы можете задать его нашему саппорту через эту форму отправки сообщений в VK. Которая находится в нижнем правом углу экрана на любой странице сайта! При обращении в поддержку ОБЯЗАТЕЛЬНО указывайте все возможные данные, добавляйте скины операций или чеки. Поддержка не имеет права не ответить на ваш запрос, но от полноты указанных данные время обработки вашего запроса сокращается в разы!
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