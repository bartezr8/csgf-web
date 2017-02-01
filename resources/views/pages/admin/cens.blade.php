@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\AdminController::TITLE_UP }}</title>

<link href="{{ $asset('assets/css/admin.css') }}" rel="stylesheet">
@include('includes.admin_head')
<div class="content">
    <div class="title-block">
        <h2 style="color: #ffffff;">
            Цензура - запрещенные слова!
        </h2>
    </div>
    <div class="black-txt-info " style="width: 100%;float: left; margin-top: 15px; margin-bottom: 5px;">
        Напиши "-" в замену для удаления слова!
    </div>
    <div style="margin-top: 15px;" class="nSend">
        <input type="text" id="word" style="overflow: hidden;width:427px;" cols="50" placeholder="Слово" value="" autocomplete="off">
        <input type="text" id="repl" style="overflow: hidden;width:427px;" cols="50" placeholder="Замена" value="" autocomplete="off">
        <input type="submit" id="sub" value="Добавить">
    </div>
    <br><br><br>
    <div class="user-winner-block">
        <div class="user-winner-table">
            <table>
                <thead>
                    <tr>
                        <td style="width: 50%;">Слово</td>
                        <td style="width: 50%;">Замена</td>
                    </tr>
                </thead>
                <div id="steamid64" style="display:none;"></div>
                <tbody id="usertable">
                </tbody>
            </table>
        </div>
    </div>
    <script>
    function udpw(data){
        $.ajax({
            url: '/admin/cens/add',
            type: 'post',
            dataType: 'json',
            data: {
                word: data,
                repl: $('#repl').val()
            },
            success: function (data) {
                updateWords();
                $.notify(data.msg, {
                    className: "success"
                });
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
            }
        });
    }
    function updateWords() {
        $.ajax({
            url: '/admin/cens/getwords',
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                $('#usertable').html('');
                console.log(data);
                for (key in data) {
                    $('#usertable').prepend("<tr><td class=\"win-count\" onclick=\"udpw( '" + data[key].text + "' )\">" + data[key].text + "</td><td class=\"participations\">" + data[key].repl + "</td></tr>");
                }
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
            }
        });
    }
    $(document).on('click', '#sub', function () {
        $.ajax({
            url: '/admin/cens/add',
            type: 'post',
            dataType: 'json',
            data: {
                word: $('#word').val(),
                repl: $('#repl').val()
            },
            success: function (data) {
                updateWords();
                $.notify(data.msg, {
                    className: "success"
                });
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
            }
        });
    });
    $(document).ready(function() {
        updateWords();
    });
    </script>
    
</div>
@endsection