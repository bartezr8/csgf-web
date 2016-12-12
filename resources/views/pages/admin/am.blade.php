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
                        <?php for($i = 1; $i<= 505; $i++)echo "<img id=\"smile\" class=\"smile-smile-_".$i."_\" onclick=\"add_smile(':sm".$i.":')\">"; ?>
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