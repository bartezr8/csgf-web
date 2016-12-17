@extends('layout')

@section('content')
<title>  {{ $title = 'Монетка | ' }}</title>
<link href="{{ $asset('assets/css/coin.css') }}" rel="stylesheet">
<script src="{{ $asset('assets/js/coin.js') }}"></script>
<div class="content">
    <div class="title-block">
        <h2 style="color: #ffffff;">Монетка</h2>
    </div>
    <div class="page-content"style="">
    
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> СОЗДАНИЕ КОМНАТЫ</b></div>
    
        <div class="add-balance-block" style="text-align: center;padding: 12px 0px 10px;">
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                Комиссия {{ config('mod_game.comission') }}%
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                Минимальная ставка:
                <span class="" style="font-size: 20px;font-weight: 600;color: #d1ff78;">0.01 </span> <div class="price-currency" style="display: inline;text-transform: uppercase;font-size: 11px;">руб.</div>
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                Ваш баланс:
                <span class="userBalance" style="font-size: 20px;font-weight: 600;color: #d1ff78;">{{ $u->money }} </span> <div class="price-currency" style="display: inline;text-transform: uppercase;font-size: 11px;">руб.</div>
            </div>
            <span class="icon-arrow-right" style="height: 55px;margin: 0 10px;display: inline-block;vertical-align: middle;"></span>
            <div class="input-group" style="display: inline-block;vertical-align: middle;">
                <input type="text" style="padding: 0 20px 0px 10px;width: 90px;height: 30px;display: inline-block;vertical-align: middle;background-color: rgba(27,42,53,0.68);border: 1px solid rgba(93,103,113,0.71);color: #b7d5e7;" id="coin_sum" pattern="^[ 0-9.]+$" maxlength="5" placeholder="Cумма">
                <button type="submit" class="btn-add-balance" id="coin_bet">Создать</button>
            </div>
        </div>
        <script type="text/template" id="coin-template">
            <tr id="coin_<%= id %>">
                <td class="participations">
                    <div class="count-block" id="sum"><%= sum %></div>
                </td>
                <td id="first" class="winner-name" >
                    <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                    <span id="user-name" style="max-width:200px;"><%= name %></span>
                </td>
                <td class="participations">
                    <button type="submit" class="btn-add-balance" id="coin_tp" onclick=" coin_bet( <%= id %> ); " >Участвовать</button>
                    <div class="flip-container" id="f1" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flip-container" id="f2" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="<%= ava %>"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flip-container" id="f3" style="display:none;">
                        <div class="flipper">
                            <div class="front">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                            <div class="back">
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td id="second" class="winner-name" >
                    <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                    <span id="user-name" style="max-width:200px;">...</span>
                </td>
                <td class="participations">
                    <div class="count-block" ><%= sum %></div>
                </td>
            </tr>
        </script>
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> АКТИВНЫЕ КОМНАТЫ</b></div>
        <div class="user-winner-block">
            <div class="user-winner-table" style="padding-bottom: 10px;">
                <table>
                    <thead>
                        <tr>
                            <td>Сумма (руб.)</td>
                            <td class="winner-name-h" style="text-align: center; padding-left: 0px; width:250px;">Создатель</td>
                            <td>Игра</td>
                            <td class="winner-name-h" style="text-align: center; padding-left: 0px; width:250px;">Участник</td>
                            <td>Сумма (руб.)</td>
                        </tr>
                    </thead>
                    <tbody id="cointable">
                    @if($coingames != NULL )
                    @forelse($coingames as $game)
                        <tr id="coin_{{ $game['id'] }}">
                            <td class="participations">
                                <div class="count-block" id="sum">{{ $game['sum'] }}</div>
                            </td>
                            <td id="first" class="winner-name" >
                                <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                <span id="user-name" style="max-width:200px;">{{ $game['name'] }}</span>
                            </td>
                            <td class="participations">
                                <button type="submit" class="btn-add-balance" id="coin_tp" onclick=" coin_bet( {{ $game['id'] }} ); " >Участвовать</button>
                                <div class="flip-container" id="f1" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-container" id="f2" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="{{ $game['ava'] }}"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-container" id="f3" style="display:none;">
                                    <div class="flipper">
                                        <div class="front">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                        <div class="back">
                                            <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td id="second" class="winner-name" >
                                <div class="user-ava"><img id="user-ava" src="/assets/img/blank.jpg"></div>
                                <span id="user-name" style="max-width:200px;">...</span>
                            </td>
                            <td class="participations">
                                <div class="count-block" >{{ $game['sum'] }}</div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    @else
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection