@extends('layout')

<div class="user-history-block">
@section('content')
       <div class="title-block">
         <h2>История игр</h2>
           </div>
             <div class="user-history-content">
            @forelse($games as $game)
               <div class="prize-container">
                 <div class="prize-head">
                     <div class="left-block">
                          <div class="prize-number">
                          <a href="/game/{{ $game->id }}">Игра <span>#{{ $game->id }}</span></a>
                          <a href="/game/{{ $game->id }}" class="round-history">История игры</a>
                          </div>
                     <div class="prize-info">
                          <div class="winner-name">
                          <span class="chance chance-two">с шансом <span>{{ \App\Http\Controllers\GameController::_getUserChanceOfGame($game->winner, $game) }}%</span></span>
                          Победил:
                          <div class="img-wrap"><img src="{{ $game->winner->avatar }}" />
                          </div>
                          <a href="/user/{{ $game->winner->steamid64 }}" class="user-name">{{ $game->winner->username }}</a>
                          </div>
                    <div class="round-sum">
                        Банк:
                        <span>{{ $game->price }}</span> рублей
                    </div>
                </div>
            </div>

            <div class="right-block" style="background: none; width: 220px;">
                <div class="publ right-content">
                    @if($game->status_prize == \App\Game::STATUS_PRIZE_WAIT_TO_SENT)
                      <span class="prize-status status-waiting">Отправка выигрыша</span>
                    @elseif($game->status_prize == \App\Game::STATUS_PRIZE_SEND)
                      <span class="prize-status status-success">Выигрыш отправлен</span>
                    @else
                      <div class="prize-status status-error">Ошибка отправки выигрыша</div>
                    @endif
                </div>
            </div>

             @if($game->status_prize == \App\Game::STATUS_PRIZE_WAIT_TO_SENT)
                <div class="date color-lightyellow">{{ $game->updated_at }}</div>
             @elseif($game->status_prize == \App\Game::STATUS_PRIZE_SEND)
                <div class="date color-lightgreen">{{ $game->updated_at }}</div>
             @else
                <div class="date color-lightred">{{ $game->updated_at }}</div>
             @endif

        </div>
       </div>
            @empty
            <div class="deposit-txt-info">
                 Пока что не было ни одной игры
            </div>
            @endforelse
       </div>

        <div class="msg-wrap">
            <div class="icon-inform-white"></div>
            <div class="msg-white msg-mini">
                На этой страницы показаны последние <span>20 игр.</span> Вы можете посмотреть историю любой игры, вписав ее номер в конец ссылки
                <span class="color-lightblue-t"><span class="weight-normal">csgf.ru/game/</span>№ игры</span>
            </div>
        </div>
        <script>
            $('.prize-status').tooltip({
                html: true,
                trigger: 'hover',
                delay: {
                    show: 500,
                    hide: 500
                },
                title: function() {
                    var text = $(this).data('old-title');
                    return '<div class="tooltip-title"><span>' + text + '</span></div>';
                }
            });
        </script>
@endsection