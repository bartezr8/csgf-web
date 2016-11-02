@extends('layout')

@section('content')
 <title>  {{ $title = \App\Http\Controllers\SendController::TITLE_UP }}</title>

	 <link href="{{ asset('assets/css/support.css') }}" rel="stylesheet">
	 <link href="{{ asset('assets/css/send.css') }}" rel="stylesheet">
	<script>	

  $(document).ready(function() {
		$(".newticket").click(function () {
   
		});
	});
</script>
<div class="content">
<div class="title-block">
            <h2 style="color: #ffffff;">
				Перевод средств
            </h2>
        </div>
		
	<div class="support">
		<div style="overflow:hidden;     margin-left: 125px;">
					
			<div class="page-main-block left" style="float: left;">
				<div class="page-block">
					<div class="ref_balance2">
					<b style="font-weight:normal;font-size:17px;">Ваш Баланс</b><br>
					<span id="balance_id">{{ $u->money }}</span> Рублей
					</div>
				</div>
			</div>
				<div class="page-main-block left" style="float: left;  margin-left: 125px;">
				<div class="page-block">
					<div class="ref_balance2">
					<b class="pribilj">Ваш steamid64</b><br>
					<span id="balance_id">{{ $u->steamid64 }}</span> 
					</div>
				</div>
			</div>
		</div>
		<form action="/gmoney" method="GET">
			<div class="gameamount">
				@if(!empty($userid))
				<input type="text" name="steamid" style="margin-left: 180px;" cols="50" pattern="^[ 0-9]+$" placeholder="steamid64"  maxlength="18" autocomplete="off" value="{{$userid}}">
				@else 
				<input type="text" name="steamid" style="margin-left: 180px;" cols="50" pattern="^[ 0-9]+$" placeholder="steamid64" maxlength="18" autocomplete="off">
				@endif
				<input type="text" name="mone"  style=" margin-left: 180px;" cols="50" pattern="^[ 0-9]+$" placeholder="СУММА" maxlength="4" autocomplete="off">
				<input type="submit" style="margin-left: 180px;" name="submit" value="Перевести средства">
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		</form>

	</div>
	<br>
	<div class="title-block">
		<h2 style="color: #ffffff;">
			История переводов
		</h2>
	</div>
	@if($perevod != NULL )
	<div class="user-winner-block" style="display: block;">
		<div class="user-winner-table">
			<table>
				<thead>
					<tr>
						<td>ID</td>
						<td class="winner-name-h">Кому</td>
						<td class="round-sum-h">От кого</td>
						<td class="winner-name-h">Сколько</td>
					</tr>
				</thead>
				@forelse($perevod as $ticket)
				<tbody>
					<tr>
						<td class="winner-count">
							<a href="/support/{{$ticket->id}}" style="color: #b3e5ff;"><div class="count-block" >#{{$ticket->id}}</div></a>
						</td>
						<td class="winner-name">
							<a href="/user/{{$ticket->money_id_to}}" style="color: #b3e5ff;"><span style="max-width: 300px;">{{$ticket->money_to}}</span></a>
						</td>
						
						<td class="winner-name">
							<a href="/user/{{$ticket->money_id_from}}" style="color: #b3e5ff;"><span style="max-width: 300px;">{{$ticket->money_from}}</span></a>
						</td>
						<td class="participations">{{$ticket->money_amount}}</td>
					</tr>
				</tbody>
				@empty
				<br><center><h1 style="color: #FFF; font-weight: 300;">Переводы отсутствуют!</h1></center>
				@endforelse
			</table>
		</div>
	</div>
	@else
	<br><center><h1 style="color: #FFF; font-weight: 300;">Переводы отсутствуют!</h1></center>
	@endif

</div>


@endsection