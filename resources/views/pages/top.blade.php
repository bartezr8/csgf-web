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
@endsection