@extends('layout')

@section('content')
<div class="user-winner-block">

    <div class="title-block">
        <h2>Топ за неделю</h2>
    </div>

    <div class="user-winner-table">
        <table>
            <thead>
            <tr>
                <td>Место</td>
                <td class="winner-name-h">Профиль</td>
                <td class="participations-h">Участий</td>
                <td>Побед</td>
                <td>Win rate</td>
                <td class="round-sum-h">Сумма банков</td>
            </tr>
            </thead>
            <tbody>

				@foreach($users as $user)
					<tr>
						<td class="winner-count">
							<div class="count-block">{{ $place++ }}</div>
						</td>
						<td class="winner-name">
							<div class="user-ava">
								<img src="{{ $user->avatar }}">
							</div>
							<a href="/user/{{ $user->steamid64 }}"><span>{{ $user->username }}</span></a>
						</td>
						<td class="participations">{{ $user->games_played }}</td>
						<td class="win-count">{{ $user->wins_count }}</td>
						<td class="winrate">{{ $user->win_rate }}%</td>
						<td class="round-sum">{{ round($user->top_value) }}</td>
				   </tr>
			   @endforeach
		   </tbody>
		</table>
	</div>
    <div class="title-block">
        <h2>Топ рефералов</h2>
    </div>

    <div class="user-winner-table">
        <table>
            <thead>
            <tr>
                <td>Место</td>
                <td class="winner-name-h">Профиль</td>
                <td class="participations-h">Пригласил</td>
                <td class="round-sum-h">Профит</td>
				<td class="participations-h">Реферал</td>
            </tr>
            </thead>
            <tbody>

				@foreach($referals as $user)
					<tr>
						<td class="winner-count">
							<div class="count-block">{{ $refplace++ }}</div>
						</td>
						<td class="winner-name">
							<div class="user-ava">
								<img src="{{ $user->avatar }}">
							</div>
							<a href="/user/{{ $user->steamid64 }}"><span>{{ $user->username }}</span></a>
						</td>
						<td class="participations">{{ $user->refcount }}</td>
						<td class="win-count">{{ $user->refprofit }}</td>
						<td class="winrate">{{ $user->refkode }}</td>
				   </tr>
			   @endforeach
		   </tbody>
		</table>
	</div>
    <div class="title-block">
        <h2>Топ на раздаче</h2>
    </div>

    <div class="user-winner-table">
        <table>
            <thead>
            <tr>
                <td>Место</td>
                <td class="winner-name-h">Профиль</td>
                <td class="participations-h">Время в раздаче</td>
                <td class="round-sum-h">Участий</td>
				<td class="participations-h">Доход</td>
            </tr>
            </thead>
            <tbody>

				@foreach($gouts as $user)
					<tr>
						<td class="winner-count">
							<div class="count-block">{{ $outplace++ }}</div>
						</td>
						<td class="winner-name">
							<div class="user-ava">
								<img src="{{ $user->avatar }}">
							</div>
							<a href="/user/{{ $user->steamid64 }}"><span>{{ $user->username }}</span></a>
						</td>
						<td class="participations">{{ $user->count * 6 }} час(-ов)</td>
						<td class="win-count">{{ $user->count }}</td>
						<td class="participations">{{ $user->top_value }}</td>
				   </tr>
			   @endforeach
		   </tbody>
		</table>
	</div>
    <div class="title-block">
        <h2>Топ за все время</h2>
    </div>

    <div class="user-winner-table">
        <table>
            <thead>
            <tr>
                <td>Место</td>
                <td class="winner-name-h">Профиль</td>
                <td class="participations-h">Участий</td>
                <td>Побед</td>
                <td>Win rate</td>
                <td class="round-sum-h">Сумма банков</td>
            </tr>
            </thead>
            <tbody>

				@foreach($userst as $user)
					<tr>
						<td class="winner-count">
							<div class="count-block">{{ $tplace++ }}</div>
						</td>
						<td class="winner-name">
							<div class="user-ava">
								<img src="{{ $user->avatar }}">
							</div>
							<a href="/user/{{ $user->steamid64 }}"><span>{{ $user->username }}</span></a>
						</td>
						<td class="participations">{{ $user->games_played }}</td>
						<td class="win-count">{{ $user->wins_count }}</td>
						<td class="winrate">{{ $user->win_rate }}%</td>
						<td class="round-sum">{{ round($user->top_value) }}</td>
				   </tr>
			   @endforeach
		   </tbody>
		</table>
	</div>
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