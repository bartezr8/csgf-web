@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\AdminController::TITLE_UP }}</title>

<link href="{{ $asset('assets/css/admin.css') }}" rel="stylesheet">
<div class="admin-container">
    <div class="admin-top">
        <div class="logotype active">
        </div>
        <div class="admin-menu">
            <ul id="headNav" class="list-reset">
                <li class="faq"><a href="/admin/"><img src="/assets/img/stav.png" alt=""> Главная страница</a></li>
                <li class="faq"><a href="/admin/users/"><img src="/assets/img/user.png" alt=""> Пользователи</a></li>
                <li class="faq"><a href="/admin/am/"><img src="/assets/img/tp.png" alt=""> Антимат</a></li>
                <li class="faq"><a href="/shop/admin/"><img src="/assets/img/php.png" alt=""> История обменов</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="content">
    <div class="title-block">
        <h2 style="color: #ffffff;">
            Панель Управления
        </h2>
    </div>
    @if($u->is_admin==1)
    <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Управление классик</b></div>
    <div class="support" >
        <div style="width: 166px" class="nSend">
            <input type="text" id="time" cols="50" style="width: 83px" cols="50" placeholder="Время" maxlength="18" autocomplete="off">
            <input type="submit" style="width: 83px" onclick="postReq('/admin/ctime', {time:$('#time').val()})" value="Изменить">
        </div>
        <div style="width: 166px" class="nSend">
            <input type="submit" style="width: 166px" onclick="postReq('/admin/clearQueue', {})" value="Очистить Redis">
        </div>
        <div style="width: 333px" class="nSend">
            <input type="text" id="rid" cols="50" style="width: 166px" cols="50" placeholder="Номер билета" maxlength="18" autocomplete="off">
            <input type="submit" onclick="postReq('/admin/winner', {id:$('#rid').val()})" style="width: 166px" value="Подкрутить">
        </div>
        <div style="width: 333px" class="nSend">
            <input type="text" id="rrid" cols="50" style="width: 166px" cols="50" placeholder="Число раунда" maxlength="18" value="0.55" autocomplete="off">
            <input type="submit" onclick="postReq('/admin/winnerr', {id:$('#rrid').val()})" style="width: 166px" value="Число раунда">
        </div>
    </div>
    <br>
    <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Рассылка ВК</b></div>
    <div class="support" >
        <div style="width: 999px" class="nSend">
            <input type="text" id="vktext" cols="50" style="width: 851px" cols="50" placeholder="Текст сообщения" value="" autocomplete="off">
            <input type="submit" onclick="postReq('/api/vk/sendText', {text:$('#vktext').val()})" style="width: 148px" value="Разослать">
        </div>
    </div>
    <br>
    <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Управление магазином</b></div>
    <div class="support" >
        <div style="width: 333px" class="nSend">
            <input type="text" id="usid" cols="50" style="width: 115px" cols="50" placeholder="ID бота" maxlength="18" autocomplete="off">
            <input type="submit" style="width: 218px" onclick="postReq('/shop/admin/updateShop', {id:$('#usid').val()})" value="Обновить магазин">
        </div>
        <div style="width: 333px" class="nSend">
            <input type="text" id="csid" cols="50" style="width: 145px" cols="50" placeholder="ID бота" maxlength="18" autocomplete="off">
            <input type="submit" style="width: 188px" onclick="postReq('/shop/admin/clearShop', {id:$('#csid').val()})" value="Удалить из магазина">
        </div>
        <div style="width: 333px" class="nSend">
            <input type="text" id="udid" cols="50" style="width: 105px" cols="50" placeholder="ID трейда" maxlength="18" autocomplete="off">
            <input type="text" id="udstatus" cols="50" style="width: 80px" cols="50" placeholder="статус" maxlength="18" autocomplete="off">
            <input type="submit" style="width: 148px" onclick="postReq('/shop/admin/updateDep', {id:$('#udid').val(),status:$('#udstatus').val()})" value="Обновить депозит">
        </div>
    </div>
    @else
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> На данной странице для модераторов нет дотупных комманд.</b></div>
    @endif
    <script>
        function postReq(url, data){
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (data) {
                    $.notify("OK", {className: "success"});
                },
                error: function () {
                    $.notify("Произошла ошибка. Попробуйте еще раз", {className: "error"});
                }
            });
        }
    </script>
</div>
@endsection