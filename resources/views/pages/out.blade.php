@extends('layout')

@section('content')
<title>  {{ $title = 'Раздача | ' }}</title>
<link href="{{ asset('assets/css/support.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/send.css') }}" rel="stylesheet">
<div class="content">
	<div class="title-block">
		<h2 style="color: #ffffff;">Раздача</h2>
	</div>
	<div class="buy-cards-container" style="padding-top: 10px;">
		<div class="buy-cards-block" style="text-align:center;">
			<div style="float: left; display: inline-block">
				<div class="buy-card-item" style="float: left; margin-top: 15px;">
					<span class="cards-price-currency">Последние Халявщики</span>
				</div>
				<span class="icon-arrow-right"style="float: left;"></span>
				<div class="last-gout-block" id="last-gout-block" style="float: left; display: block; overflow: hidden; width: 738px; height: 52px;">
					@if($avatars != NULL )
						@forelse($avatars as $avatar)
							<img style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="{{$avatar}}" class="scale-in">
						@empty
						@endforelse
					@endif
				</div>
			</div>
		</div>

	</div>
		
	<div class="page-content"style="border-top: 1px solid #3D5260;">
		<div class="page-main-block" style="padding-top: 20px; width: 765px; margin-bottom: 15px;">
			<div class="page-mini-title">Как это работает:</div>
			<div class="page-block" style="padding-top: 10px;">
				<ul>
					<li>Через <b>{{ round(\App\Http\Controllers\GiveOutController::outtime / 3600) }} час</b>(-ов) после принятия участия в раздаче и соблюдения условий вы получите <b>{{\App\Http\Controllers\GiveOutController::outmoney }} руб</b>!</li>
					<li>Ваш ник должен содержать {{ strtoupper(str_replace("/", "", str_replace("://", "", str_replace("http", "", str_replace("https", "", config('app.url')))))) }}. Ник периодически обновляется поэтому его нельзя менять.<br>
					<span>Если вы поменяете ник и уберете из него название сайта то все результаты обнуляются!</span></li>
					<li>У вас должно быть <b>не меньше 2 ставок</b>. Сумма ставок не учитывается. (антинакрутка)</li>
					<li>Каждые 6 часов в раздачу нужно вступать заного. Доход вы забираете сами.</li>
				</ul>
			</div>
		</div>
		<div class="page-main-block" style="border-left: 1px solid #3D5260; padding: 20px; width: 245px; float: right; margin-top: -209px; height: 234px; background: #284351;">
		
			<input type="submit" style="float: initial; width: 203px;" id="takePart" onclick="startOut()" class="green_but" value="Участвовать" />
			<input type="submit" style="float: initial; width: 203px; display:none" id="getMon" onclick="getMon()" class="blue_but" value="Забрать доход" />
			
			<div style="padding-top: 25px;" class="timer-new" id="gameTimer">
				<span style="padding-top: 18px;width: 64px;" class="countHours">00</span>
				<span class="countDiv">:</span>
				<span style="padding-top: 18px;width: 64px;" class="countMinutes">00</span>
				<span class="countDiv">:</span>
				<span style="padding-top: 18px; padding-top: 6px;width: 33px;height: 30px;font-size: 17px;margin-top: 15px;" class="countSeconds">00</span>
			</div>
			
			<div>
				<div style="margin-top: 40px; "class="participate-info">
					<span style="font-size: 13px; font-weight: normal; color: #a5c9da;" id="notinout">Вы <span id="myItemsCount" style="font-size: 15px; color: #fc8356;">не участв.</span> в раздаче<br></span>
					<span style="font-size: 13px; display: none; font-weight: normal; color: #a5c9da;" id="inout">Вы <span id="myItemsCount" style="font-size: 15px; color: #d1ff78;">участвуете</span> в раздаче<br></span>
					Вы заработали: <span id="thisMon" style="color: #d1ff78;">0.00</span> руб.
					Всего доход: <span id="sumMon" style="color: #d1ff78;">0.00</span> руб.
				</div>
			</div>

		</div>
	</div>
</div>

<script>
	function updateOut() {
		$.post('/out/get', function (data) {
			$('#thisMon').text(data.thisMon);
			$('#sumMon').text(data.sum);
			if(data.do == 'true'){
				$('#takePart').hide();
				$('#getMon').show();
				$('#notinout').hide();
				$('#inout').show();
				if(data.val.status == 0){
					time = data.val.left;
					clearInterval(timer);
					var timer = setInterval(function () {
						time -= 1;
						if(time <= 0){
							clearInterval(timer);
							time = 0;
							$('.countHours').text(lpad(Math.floor(time / 3600), 2));
							$('.countMinutes').text(lpad(Math.floor(time / 60) - Math.floor(time / 3600) * 60, 2));
							$('.countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
							updateOut();
						}
						$('.countHours').text(lpad(Math.floor(time / 3600), 2));
						$('.countMinutes').text(lpad(Math.floor(time / 60) - Math.floor(time / 3600) * 60, 2));
						$('.countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
					}, 1000);
				}
			} else {
				$('#takePart').show();
				$('#getMon').hide();
				$('#notinout').show();
				$('#inout').hide();
				time = 0;
				$('.countHours').text(lpad(Math.floor(time / 3600), 2));
				$('.countMinutes').text(lpad(Math.floor(time / 60) - Math.floor(time / 3600) * 60, 2));
				$('.countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
				
			}
		});
	}
	function startOut() {
		$.post('/out/start', function (data) {
			setTimeout(updateOut, 1000);
			return $.notify(data.text, data.type);
		});
	}
	function getMon() {
		$.post('/out/getMon', function (data) {
			setTimeout(updateOut, 1000);
			return $.notify(data.text, data.type);
		});
	}
	$(function () {
		window.time = 0;
		updateOut();
	});
</script>

@endsection