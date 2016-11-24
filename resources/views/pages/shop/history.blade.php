@extends('layout')

@section('content')
    <div class="content-block">
        <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
        <div class="user-history-block bid-history" style="padding-top: 10px;">

            <div class="title-block">
                <h2>История ваших покупок</h2>
            </div>

            <div class="user-history-content" id="showMoreContainer">
                <div class="body-content">
                    <div class="memoMsg">
                        Если после покупки у вас в статусе написано "Ошибка" не переживайте - деньги будут возвращены на
                        баланс. Возврат средств происходит автоматически каждый час.<br>
                        Если у вас была введена не рабочая ссылка на обмен, исправьте ссылку на рабочую, дождитесь
                        возврата средств и повторите покупку.<br>
                        Если в статусе написано, что у вас бан трейда, тогда не пробуйте покупать снова, а подождите
                        пока у вас закончится ограничение на обмен и только тогда продолжайте покупки.
                    </div>

                    <div class="purchase-history-table">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата</th>
                                <th>Предмет</th>
                                <th>Качество</th>
                                <th>Цена</th>
                                <th>Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->buy_at }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->quality }}</td>
                                    <td>{{ $item->price }} руб</td>
                                    <td>
                                        @if($item->status == \App\Shop::ITEM_STATUS_SOLD)
                                            Отправка предмета
                                        @elseif($item->status == \App\Shop::ITEM_STATUS_SEND)
                                            Предмет отправлен
                                        @elseif($item->status == \App\Shop::ITEM_STATUS_FOR_SALE)
                                            Обмен истек
                                        @elseif($item->status == \App\Shop::ITEM_STATUS_NOT_FOUND)
                                            Предмет не найден
                                        @elseif($item->status == \App\Shop::ITEM_STATUS_ERROR_TO_SEND)
                                            Ошибка отправки
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Вы не делали покупок</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="pagination-history">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="user-history-block bid-history" style="padding-top: 10px;">

            <div class="title-block">
                <h2>История ваших обменов</h2>
            </div>

            <div class="user-history-content" id="showMoreContainer">
                <div class="body-content">
                    <div class="purchase-history-table">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                @if($u->is_admin==1)
                                <th>Пользователь</th>
                                @endif
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Цена</th>
                                <th>Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($deposits as $deposit)
                                <tr>
                                    <td>{{ $deposit->id }}</td>
                                    @if($u->is_admin==1)
                                    <td>{{ $deposit->user_id }}</td>
                                    @endif
                                    <td>{{ $deposit->date }}</td>
                                    <td>@if($deposit->type == 0)
                                            Депозит скинами
                                        @elseif($deposit->type == 3)
                                            Пополнение баланса
                                        @else
                                            Вывод средств
                                        @endif
                                    </td>
                                    <td>@if($deposit->type == 1)- @endif{{ $deposit->price }} руб</td>
                                    <td>Принят</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Вы не делали обменов с магазином</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="pagination-history">

                        </div>
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