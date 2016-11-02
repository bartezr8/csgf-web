@extends('layout')

@section('content')
<title>  {{ $title = \App\Http\Controllers\AdminController::TITLE_UP }}</title>
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
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
	@if($u->is_admin==1)
	<div class="title-block">
		<h2 style="color: #ffffff;">
			Панель Управления Ботом
		</h2>
	</div>
    <div class="admin-container">
        <div class="admin-top">
            <div class="logotype active">
			</div>
            <div class="admin-menu">
                <ul id="headNav" class="list-reset">
					<li class="faq">
						<div class="statusBot">
							<span id="statusBot" class="" title="" data-toggle="tooltip" data-original-title=""></span>
						</div>
					</li>
					<li class="faq">
						<a class="btn-vk" id="start">START BOT</a>
					</li>
					<li class="faq">
						<a class="btn-vk" id="stop">STOP BOT</a>
					</li>
					<li class="faq">
						<a class="btn-vk" id="restart">RESTART BOT</a>
					</li>
					<li class="faq">
						<a class="btn-vk" id="reload">RELOAD BOT</a>
					</li>
					<li class="faq">
						<a class="btn-vk" id="mysql">Redis</a>
					</li>
					<li class="faq">
						<a class="btn-vk" id="scroll">SCROLL</a>
					</li>
                </ul>
            </div>
        </div>
	</div>
	<br>
	@endif
	<div class="title-block">
		<h2 style="color: #ffffff;">
			Логи бота
		</h2>
	</div>
	<br>
	<div class="console" id="c"></div>
	<script>
	$(document).ready(function() {
		var canscroll = false;
		$(document).on('click', '#start', function () {
			$.ajax({
				url: '/admin/bot/start',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$.notify('Бот успешно запущен', {
							className: "success"
						});
					} else {
						$.notify("Ошибка: Бот уже запущен", {
							className: "error"
						});
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
		$(document).on('click', '#stop', function () {
			$.ajax({
				url: '/admin/bot/stop',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$.notify('Бот успешно остановлен', {
							className: "success"
						});
					} else {
						$.notify("Ошибка: Бот уже остановлен", {
							className: "error"
						});
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
		$(document).on('click', '#restart', function () {
			$.ajax({
				url: '/admin/bot/restart',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$.notify('Бот успешно перезапущен', {
							className: "success"
						});
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
		$(document).on('click', '#scroll', function () {
			if (canscroll){
				canscroll = false;
				$('#c').css('overflow', 'hidden');
			} else {
				canscroll = true;
				$('#c').css('overflow', 'auto');
			}
		});
		$(document).on('click', '#reload', function () {
			$.ajax({
				url: '/admin/bot/reload',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$.notify('Бот успешно перезагружен', {
							className: "success"
						});
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
		$(document).on('click', '#mysql', function () {
			$.ajax({
				url: '/admin/bot/mysql',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$.notify('База данных перезагружена', {
							className: "success"
						});
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
		$('#c').load('/admin/bot/log');
		var objDiv = document.getElementById("c");
		objDiv.scrollTop = objDiv.scrollHeight;
		logs = setInterval(function(){
			$('#c').load('/admin/bot/log');
			if (!canscroll) objDiv.scrollTop = objDiv.scrollHeight;
		}, 2000);
		bot = setInterval(function(){
			$.ajax({
				url: '/admin/bot/status',
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data == true) {
						$('#statusBot').removeAttr('title');
						$('#statusBot').attr('title', 'Включен');
						$('#statusBot').removeClass();
						$('#statusBot').addClass('online');
					} else {
						$('#statusBot').removeAttr('title');
						$('#statusBot').attr('title', 'Выключен');
						$('#statusBot').removeClass();
						$('#statusBot').addClass('offline');
					}
				},
				error: function () {
					that.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		}, 5000);
	});
	</script>
	
</div>
 
@endsection