@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\AdminController::TITLE_UP }}</title>

<link href="{{ $asset('assets/css/admin.css') }}" rel="stylesheet">
@include('includes.admin_head')
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
            <div style="width: 250px" class="nSend">
                <input type="text" id="rid" cols="50" style="width: 125px" cols="50" placeholder="Номер билета" maxlength="18" autocomplete="off">
                <input type="submit" onclick="postReq('/admin/winner', {id:$('#rid').val()})" style="width: 125px" value="Подкрутить">
            </div>
            <div style="width: 250px" class="nSend">
                <input type="text" id="rrid" cols="50" style="width: 125px" cols="50" placeholder="Число раунда" maxlength="18" value="0.55" autocomplete="off">
                <input type="submit" onclick="postReq('/admin/winnerr', {id:$('#rrid').val()})" style="width: 125px" value="Число раунда">
            </div>
            <div style="width: 166px" class="nSend">
                <input type="submit" style="width: 166px" onclick="postReq('/admin/clearQueue', {})" value="Очистить Redis">
            </div>
            <div style="width: 166px" class="nSend">
                <input type="submit" onclick="clearTables()" style="width: 166px" value="Очистка таблиц">
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
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Double</b></div>
        <div class="support" >
            <div style="width: 999px" class="nSend">
                <input type="text" id="did" cols="50" style="width: 851px" cols="50" placeholder="Победное число" value="" autocomplete="off">
                <input type="submit" onclick="postReq('/admin/double', {id:$('#did').val()})" style="width: 148px" value="Подкрутить">
            </div>
        </div>
        <br>
    @else
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> На данной странице для модераторов нет дотупных комманд.</b></div>
        <br>
    @endif
    <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> Статистика</b></div>
    <div class="page-main-block" style="border-bottom: 1px solid #3D5260; padding-bottom: 20px;">
        <br>
        <div class="adm" style="display: inline-block;padding: 15px;vertical-align: middle;">
            <div class="page-mini-title">Double:</div>
            <div class="page-block">
                <ul>
                    <li>Антиминус: {{ $data['double']['am'] }}</li>
                    <li>За все время: {{ $data['double']['total'] }}</li>
                </ul>
            </div>
        </div>
        <div class="adm" style="display: inline-block;padding: 15px;vertical-align: middle;">
            <div class="page-mini-title">Coin:</div>
            <div class="page-block">
                <ul>
                    <li>За все время: {{ $data['coin'] }}</li>
                </ul>
            </div>
        </div>
        <div class="adm" style="display: inline-block;padding: 15px;vertical-align: middle;">
            <div class="page-mini-title">Dice:</div>
            <div class="page-block">
                <ul>
                    <li>Антиминус: {{ $data['dice']['am'] }}</li>
                    <li>За все время: {{ $data['dice']['total'] }}</li>
                </ul>
            </div>
        </div>
        <div class="adm" style="display: inline-block;padding: 15px;vertical-align: middle;">
            <div class="page-mini-title">Рулетка:</div>
            <div class="page-block">
                <ul>
                    <li>За сегодня: {{ $data['classic']['total_today'] }}</li>
                    <li>Комиссия: {{ $data['classic']['comission'] }}</li>
                </ul>
            </div>
        </div>
        <div class="adm" style="display: inline-block;padding: 15px;vertical-align: middle;">
            <div class="page-mini-title">Магазин: (сегодня)</div>
            <div class="page-block">
                <ul>
                    <li>Вывод: {{ $data['shop']['withdraw'] }}</li>
                    <li>Депозит: {{ $data['shop']['deposit'] }}</li>
                    <li>Пополнене: {{ $data['shop']['pay'] }}</li>
                </ul>
            </div>
        </div>
    </div>
    
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
        function clearTables(){
            if (confirm('Вы действительно хотите очистить таблицы ')) {
                postReq('/admin/cleartables', {});
            } else { 
                alert('Замечательно!'); 
            }
        }
    </script>
</div>
@endsection