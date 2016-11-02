@extends('layout')

@section('content')
    <div class="content-block">
        <link rel="stylesheet" href="{{ asset('assets/css/shop.css') }}"/>
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
@endsection