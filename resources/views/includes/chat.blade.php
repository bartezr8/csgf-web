<!-- Chat -->
    <div id="chatContainer" class="chat-with-prompt" style="display: none;box-shadow: 0 0 10px #1E2127;">
        <span id="chatClose" class="chat-close"></span>
        <div id="chatHeader">Чат</div>
        <div class="chat-prompt" id="chat-prompt" style="font-size: 16px;font-weight: 600;text-shadow: 0 2px 2px rgba(0, 0, 0, 0.26);">
            <div class="chat-prompt-top"><span class="title">ONLINE:</span> <span style="color: #00fdff" id="count_online">0</span></div>
            <div class="chat-prompt-mid">
                <div style="margin-top:7px;margin-bottom: 9px;text-align: -webkit-auto;border-bottom: 1px solid #2D4455;">
                    <ul>
                        <li style="list-style: none;"><span class="title">В очереди:</span> <span style="color: #00fdff" id="count_trades">0</span> <span class="title">трейдов</span></li>
                    </ul>
                </div>
            </div>
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
                        <?php for($i = 1; $i<= 505; $i++)echo "<a id=\"smile\" class=\"smile-smile-_".$i."_\" onclick=\"add_smile(':sm".$i.":')\"></a>"; ?>
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
                                        <li style="margin-bottom: 5px;">Упоминание своего реферала без просьбы других игроков;</li>
                                        <li style="margin-bottom: 5px;">Оскорблять других игроков, модераторо или создателей;</li>
                                        <li style="margin-bottom: 5px;">Оставлять ссылки на сторонние ресурсы и просить перейти;</li>
                                        <li style="margin-bottom: 5px;">Выпрашивать скины у других участников игрового процесса;</li>
                                        <li style="margin-bottom: 5px;">Обвинять и рулетку в нечествности игры, подкрутке;</li>
                                    </ul>
                                </div>
                                <div class="page-mini-title">Любое нарушение видет к муту.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Chat END -->