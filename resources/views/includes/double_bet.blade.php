<li class="dl fade-in-down">
	<div style="overflow: hidden;line-height:32px">
		<a href="/user/{{ $user->steamid64 }}" target="_blank">
			<div class="pull-left">
				<img class="rounded" src="{{ $user->avatar }}"> <span style="color: white;">{{ $user->username }}</span>
			</div>
			<div class="amount pull-right"><b style="color: white;">{{ $bet->price }} </b></div>
		</a>
	</div>
</li>