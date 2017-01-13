@extends('layout')

@section('content')
<title>ПУ гифтами</title>
<link href="{{ $asset('assets/css/admin.css') }}" rel="stylesheet">
<div class="content-block">
    <link rel="stylesheet" href="{{ $asset('assets/css/shop.css') }}"/>
    <div class="user-history-block bid-history" style="padding-top: 10px;">
        <div class="title-block">
            <h2>ПУ гифтами</h2>
        </div>
        <div class="support" >
            <form action="/gifts/admin/select" method="GET">
                <div style="width: 999px" class="nSend">
                    <input type="text" name="user" cols="50" style="width: 333px" cols="50" placeholder="User ID" maxlength="18" autocomplete="off">
                    <input type="text" name="id" cols="50" style="width: 333px" cols="50" placeholder="Gift ID" maxlength="18" autocomplete="off">
                    <input type="submit" style="width: 333px" value="Назначить победителя">
                </div>
            </form>
        </div>
        <br>
        <div class="user-history-content" id="showMoreContainer">
            <div class="body-content">
                <div class="purchase-history-table">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Игра</th>
                            <th>Цена</th>
                            <th>Потрачено</th>
                            <th>Дата</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>Получено</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($gifts as $gift)
                            <tr>
                                <td>{{ $gift->id }}</td>
                                <td>{{ $gift->user_id }}</td>
                                <td>{{ $gift->game_name }}</td>
                                <td>{{ $gift->store_price }}</td>
                                <td>{{ $gift->buy_price }}</td>
                                <td>{{ $gift->sold_at }}</td>
                                <td>{{ $gift->game_type }}</td>
                                <td>{{ $gift->sold }}</td>
                                <td>{{ $gift->received }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">Гифтов в базе нет</td>
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