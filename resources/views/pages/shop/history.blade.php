@extends('layout')

@section('content')
    <div class="content-block">
        <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
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
                                <th>User ID</th>
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
                                    <td>{{ $deposit->user_id }}</td>
                                    <td>{{ $deposit->date }}</td>
                                    <td>@if($deposit->type == \App\Shop::D_DEPOSIT)
                                            Депозит скинами
                                        @elseif($deposit->type == \App\Shop::D_BUY)
                                            Вывод средств
                                        @elseif($deposit->type == \App\Shop::D_RETURN)
                                            Возврат средств
                                        @else
                                            Пополнение баланса
                                        @endif
                                    </td>
                                    <td>@if($deposit->type == \App\Shop::D_BUY)- @endif{{ $deposit->price }} руб</td>
                                    <td>Принят</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">У вас небыло операций с изменением баланса</td>
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
                <h2>История ваших депозитов в магазин</h2>
            </div>

            <div class="user-history-content" id="showMoreContainer">
                <div class="body-content">
                    <div class="purchase-history-table">
                        <table>
                            <thead>
                            
                            <tr>
                                <th>ID</th>
                                <th>Дата</th>
                                <th>User ID</th>
                                <th>Бот №</th>
                                <th>ID обмена</th>
                                <th>Цена</th>
                                <th>Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($shop_offers as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $item->user_id }}</td>
                                    <td>{{ $item->bot_id }}</td>
                                    <td>{{ $item->tradeid }}</td>
                                    <td>{{ $item->price }} руб</td>
                                    <td>
                                        @if($item->status == 0)
                                            Активен
                                        @elseif($item->status == 1)
                                            Зачислен
                                        @elseif($item->status == 2)
                                            Отклонен
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">Вы не делали депозитов</td>
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
                <h2>История ваших покупок</h2>
            </div>

            <div class="user-history-content" id="showMoreContainer">
                <div class="body-content">
                    <div class="memoMsg">
                        Если после покупки у вас в статусе написано "Ошибка" - деньги за предмет уже возвращены.<br>
                        Если у вас была введена не рабочая ссылка на обмен, исправьте ссылку на рабочую и повторите покупку.<br>
                        Если в статусе написано, что у вас бан трейда, тогда не пробуйте покупать снова, а подождите
                        пока у вас закончится ограничение на обмен и только тогда продолжайте покупки.
                    </div>

                    <div class="purchase-history-table">
                        <table>
                            <thead>
                            <tr>
                                <th style="width: 1%;">ID</th>
                                <th style="width: 2%;">Дата</th>
                                <th style="width: 1%;">Бот №</th>
                                <th style="width: 5%;">Предмет</th>
                                <th style="width: 1%;">Качество</th>
                                <th style="width: 2%;">Цена</th>
                                <th style="width: 3%;">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->buy_at }}</td>
                                    <td>{{ $item->bot_id }}</td>
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
                                    <td colspan="7">Вы не делали покупок</td>
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
@endsection