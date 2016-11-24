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
                </ul>
            </div>
        </div>
	</div>
<div class="content">
	<div class="title-block">
		<h2 style="color: #ffffff;">
			Управление пользователями
		</h2>
	</div>
	<div style="margin-top: 15px;" class="nSend">
		<input type="text" id="steamid" style="overflow: hidden;width:787px;" cols="50" placeholder="SteamID64" value="{{ $user->steamid64 }}" maxlength="18" autocomplete="off">
		<input type="submit" id="sub" value="Выбрать">
		<input type="submit" style="width:34px;" id="fuadd" value="+">
		<input type="submit" style="width:34px;" id="fudel" value="-">
	</div>
	<br><br><br>
	<div id="minDepositMessage" class="msg-wrap">
		<div class="black-txt-info " style="width: 49%;float: left; margin-top: 15px; margin-right: 5px; margin-bottom: 5px;">
			Мут - бан в чате.
		</div>
		<div class="black-txt-info " style="width: 49%;float: left; margin-top: 15px; margin-right: 5px; margin-bottom: 5px;">
			Бан - бан в магазе, дабле, игре.
		</div>
	</div>
	<div class="user-winner-block">
		<div class="user-winner-table">
			<table>
				<thead>
					<tr>
						<td>ID</td>
						<td class="winner-name-h">Профиль</td>
						<td>Мут</td>
						<td>Бан</td>
						<td>Реферов</td>
						<td>Баланс</td>
						<td>А</td>
						<td>М</td>
					</tr>
				</thead>
				<div id="steamid64" style="display:none;">{{ $user->steamid64 }}</div>
				<tbody id="usertable">
					<tr>
						<td class="winner-count">
							<a id="href" href="/user/{{ $user->steamid64 }}" style="color: #b3e5ff;"><div class="count-block" >{{ $user->id }}</div></a>
						</td>
						<td class="winner-name" >
							<div class="user-ava"><img id="user-ava" src="{{ $user->avatar }}"></div>
							<span id="user-name" style="max-width:150px;">{{ $user->username }}</span>
						</td>
						<td class="participations"><input onchange="updateMute(this.value)" class="ainput" type="text" id="banc" style="overflow: hidden;width:100%; text-align: center;" cols="50" placeholder="{{ $user->banchat }}" maxlength="18" value="" autocomplete="off"></td>
						<td class="win-count"><input onchange="updateBan(this.value)" class="ainput" type="text" id="ban" style="overflow: hidden;width:100%; text-align: center;" cols="50" placeholder="{{ $user->ban }}" maxlength="18" value="" autocomplete="off"></td>
						<td id="ref" class="participations">{{ $user->refcount }}</td>
						<td class="win-count"><input @if($u->is_admin==0)readonly @endif onchange="updateMoney(this.value)" class="ainput" type="text" id="money" style="overflow: hidden;width:100%; text-align: center;" cols="50" placeholder="{{ $user->money }}" maxlength="18" value="" autocomplete="off"></td>
						<td class="participations"><input @if($u->is_admin==0)readonly @endif onchange="updateAdmin(this.value)" class="ainput" type="text" id="isa" style="overflow: hidden;width:100%; text-align: center;" cols="50" placeholder="{{ $user->is_admin }}" maxlength="18" value="" autocomplete="off"></td>
						<td class="win-count"><input @if($u->is_admin==0)readonly @endif onchange="updateModerator(this.value)" class="ainput" type="text" id="ism" style="overflow: hidden;width:100%; text-align: center;" cols="50" placeholder="{{ $user->is_moderator }}" maxlength="18" value="" autocomplete="off"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$(document).on('click', '#sub', function () {
			$('#usertable').slideUp();
			$.ajax({
				url: '/admin/userinfo',
				type: 'POST',
				dataType: 'json',
				data: {steamid: $('#steamid').val()},
				success: function (data) {
					if(data){
						$("#href").attr("href", "/user/" + data.steamid64);
						$('#steamid').text(data.steamid64);
						$('.count-block').text(data.id);
						$('#user-name').text(data.username);
						$("#user-ava").attr("src", data.avatar);
						$('#ref').text(data.refcount);
						$('#steamid64').text(data.steamid64);
						
						$("#money").attr("placeholder", data.money);
						$("#money").val('');
						
						$("#banc").attr("placeholder", data.banchat);
						$("#banc").val('');
						
						$("#ban").attr("placeholder", data.ban);
						$("#ban").val('');
						
						$("#btct").attr("placeholder", data.ban_ticket);
						$("#btct").val('');
						
						$("#isa").attr("placeholder", data.is_admin);
						$("#isa").val('');
						
						$("#ism").attr("placeholder", data.is_moderator);
						$("#ism").val('');
						$('#usertable').slideDown();
					}
				},
				error: function () {
					$.notify("Произошла ошибка. Попробуйте еще раз", {
						className: "error"
					});
				}
			});
		});
	});		
	$(document).on('click', '#fuadd', function () {
		$.ajax({
			url: '/admin/fuser_add',
			type: 'POST',
			dataType: 'json',
			data: {steamid: $('#steamid').val()},
			success: function (data) {
				$.notify("Пользователь добавлен", {
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
	$(document).on('click', '#fudel', function () {
		$.ajax({
			url: '/admin/fuser_del',
			type: 'POST',
			dataType: 'json',
			data: {steamid: $('#steamid').val()},
			success: function (data) {
				$.notify("Пользователь удален", {
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
	function updateMute(value) {
		$.ajax({
			url: '/admin/users/updateMute',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#banc").attr("placeholder", data.value);
				$("#banc").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
	function updateBan(value) {
		$.ajax({
			url: '/admin/users/updateBan',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#ban").attr("placeholder", data.value);
				$("#ban").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
	function updateBanSup(value) {
		console.log('S64:' + $('#steamid64').text());
		console.log('VAL:' + value);
		$.ajax({
			url: '/admin/users/updateBanSup',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#btct").attr("placeholder", data.value);
				$("#btct").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
	function updateMoney(value) {
		console.log('S64:' + $('#steamid64').text());
		console.log('VAL:' + value);
		$.ajax({
			url: '/admin/users/updateMoney',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#money").attr("placeholder", data.value);
				$("#money").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
	function updateAdmin(value) {
		$.ajax({
			url: '/admin/users/updateAdmin',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#isa").attr("placeholder", data.value);
				$("#isa").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
	function updateModerator(value) {
		$.ajax({
			url: '/admin/users/updateModerator',
			type: 'POST',
			dataType: 'json',
			data: {
				steamid: $('#steamid64').text(),
				value: value
			},
			success: function (data) {
				$.notify("Данные изменены", {
					className: "success"
				});
				$("#ism").attr("placeholder", data.value);
				$("#ism").val('');
			},
			error: function () {
				$.notify("Произошла ошибка. Попробуйте еще раз", {
					className: "error"
				});
			}
		});
    }
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