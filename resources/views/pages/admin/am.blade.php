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
					<li class="faq"><a href="/admin/am/"><img src="/assets/img/user.png" alt=""> Антимат</a></li>
                    <li class="faq"><a href="/admin/bot/"><img src="/assets/img/tp.png" alt=""> Управление Ботом</a></li>		
					<li class="faq"><a href="/pma/" target="_blank"><img src="/assets/img/php.png" alt=""> PhpMyAdmin</a></li>
                </ul>
            </div>
        </div>
	</div>
<div class="content">
	<div class="title-block">
		<h2 style="color: #ffffff;">
			Антимат - запрещенные слова!
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
			url: '/admin/am/add',
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
			url: '/admin/am/getwords',
			type: 'POST',
			dataType: 'json',
			success: function (data) {
				$('#usertable').html('');
				for (key in data) {
					$('#usertable').prepend("<tr><td class=\"win-count\" onclick=\"udpw( '" + key + "' )\">" + key + "</td><td class=\"participations\">" + data[key] + "</td></tr>");
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
			url: '/admin/am/add',
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