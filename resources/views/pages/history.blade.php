@extends('layout')

<div class="user-history-block">
@section('content')
       <div class="title-block">
         <h2>История игр</h2>
           </div>
             <div class="user-history-content">
            @forelse($games as $game)
               <div class="prize-container">
                 <div class="prize-head">
                     <div class="left-block">
                          <div class="prize-number">
                          <a href="/game/{{ $game->id }}">Игра <span>#{{ $game->id }}</span></a>
                          <a href="/game/{{ $game->id }}" class="round-history">История игры</a>
                          </div>
                     <div class="prize-info">
                          <div class="winner-name">
                          <span class="chance chance-two">с шансом <span>{{ \App\Http\Controllers\GameController::_getUserChanceOfGame($game->winner, $game) }}%</span></span>
                          Победил:
                          <div class="img-wrap"><img src="{{ $game->winner->avatar }}" />
                          </div>
                          <a href="/user/{{ $game->winner->steamid64 }}" class="user-name">{{ $game->winner->username }}</a>
                          </div>
                    <div class="round-sum">
                        Банк:
                        <span>{{ $game->price }}</span> рублей
                    </div>
                </div>
            </div>

            <div class="right-block" style="background: none; width: 220px;">
                <div class="publ right-content">
                    @if($game->status_prize == \App\Game::STATUS_PRIZE_WAIT_TO_SENT)
                      <span title="Отправка предметов" class="prize-status status-waiting">Отправка выигрыша</span>
                    @elseif($game->status_prize == \App\Game::STATUS_PRIZE_SEND)
                      <span title="Выигрыш отправлен" class="prize-status status-success">Выигрыш отправлен</span>
                    @else
                      <div title="{{ $game->msg }}" class="prize-status status-error">Ошибка отправки выигрыша</div>
                    @endif
                </div>
            </div>

             @if($game->status_prize == \App\Game::STATUS_PRIZE_WAIT_TO_SENT)
                <div class="date color-lightyellow">{{ $game->updated_at }}</div>
             @elseif($game->status_prize == \App\Game::STATUS_PRIZE_SEND)
                <div class="date color-lightgreen">{{ $game->updated_at }}</div>
             @else
                <div class="date color-lightred">{{ $game->updated_at }}</div>
             @endif

        </div>
       </div>
            @empty
            <div class="deposit-txt-info">
                 Пока что не было ни одной игры
            </div>
            @endforelse
       </div>

        <div class="msg-wrap">
            <div class="icon-inform-white"></div>
            <div class="msg-white msg-mini">
                На этой страницы показаны последние <span>20 игр.</span> Вы можете посмотреть историю любой игры, вписав ее номер в конец ссылки
                <span class="color-lightblue-t"><span class="weight-normal">csgf.ru/game/</span>№ игры</span>
            </div>
        </div>
        <script>
            $('.prize-status').tooltip({
                html: true,
                trigger: 'hover',
                delay: {
                    show: 500,
                    hide: 500
                },
                title: function() {
                    var text = $(this).data('old-title');
                    return '<div class="tooltip-title"><span>' + text + '</span></div>';
                }
            });
        </script>
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
                        <?php for($i = 1; $i<= 505; $i++)echo "<img id=\"smile\" class=\"smile-smile-_".$i."_\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
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