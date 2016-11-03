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
        <div class="add-balance-block" style="text-align: center;padding: 12px 0px 10px;border-bottom: 1px solid #3D5260;">
            <div class="balance-item" style="font-size: 14px;color: #7995a8;font-weight: 400;display: inline-block;vertical-align: middle;">
                БЕЗ КОМИССИИ
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
                    <div class="count-block" ><%= sum %></div>
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
                                <div class="count-block" >{{ $game['sum'] }}</div>
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
                        <img src="/assets/img/smiles/smile (1).png" id="smile" style="background:none;" onclick="add_smile(':sm1:')">
                        <img src="/assets/img/smiles/smile (2).png" id="smile" style="background:none;" onclick="add_smile(':sm2:')">
                        <img src="/assets/img/smiles/smile (3).png" id="smile" style="background:none;" onclick="add_smile(':sm3:')">
                        <img src="/assets/img/smiles/smile (4).png" id="smile" style="background:none;" onclick="add_smile(':sm4:')">
                        <img src="/assets/img/smiles/smile (5).png" id="smile" style="background:none;" onclick="add_smile(':sm5:')">
                        <img src="/assets/img/smiles/smile (6).png" id="smile" style="background:none;" onclick="add_smile(':sm6:')">
                        <img src="/assets/img/smiles/smile (7).png" id="smile" style="background:none;" onclick="add_smile(':sm7:')">
                        <img src="/assets/img/smiles/smile (8).png" id="smile" style="background:none;" onclick="add_smile(':sm8:')">
                        <img src="/assets/img/smiles/smile (9).png" id="smile" style="background:none;" onclick="add_smile(':sm9:')">
                        <img src="/assets/img/smiles/smile (10).png" id="smile" style="background:none;" onclick="add_smile(':sm10:')">
                        <img src="/assets/img/smiles/smile (11).png" id="smile" style="background:none;" onclick="add_smile(':sm11:')">
                        <img src="/assets/img/smiles/smile (12).png" id="smile" style="background:none;" onclick="add_smile(':sm12:')">
                        <img src="/assets/img/smiles/smile (13).png" id="smile" style="background:none;" onclick="add_smile(':sm13:')">
                        <img src="/assets/img/smiles/smile (14).png" id="smile" style="background:none;" onclick="add_smile(':sm14:')">
                        <img src="/assets/img/smiles/smile (15).png" id="smile" style="background:none;" onclick="add_smile(':sm15:')">
                        <img src="/assets/img/smiles/smile (16).png" id="smile" style="background:none;" onclick="add_smile(':sm16:')">
                        <img src="/assets/img/smiles/smile (17).png" id="smile" style="background:none;" onclick="add_smile(':sm17:')">
                        <img src="/assets/img/smiles/smile (18).png" id="smile" style="background:none;" onclick="add_smile(':sm18:')">
                        <img src="/assets/img/smiles/smile (19).png" id="smile" style="background:none;" onclick="add_smile(':sm19:')">
                        <img src="/assets/img/smiles/smile (20).png" id="smile" style="background:none;" onclick="add_smile(':sm20:')">
                        <img src="/assets/img/smiles/smile (21).png" id="smile" style="background:none;" onclick="add_smile(':sm21:')">
                        <img src="/assets/img/smiles/smile (22).png" id="smile" style="background:none;" onclick="add_smile(':sm22:')">
                        <img src="/assets/img/smiles/smile (23).png" id="smile" style="background:none;" onclick="add_smile(':sm23:')">
                        <img src="/assets/img/smiles/smile (24).png" id="smile" style="background:none;" onclick="add_smile(':sm24:')">
                        <img src="/assets/img/smiles/smile (25).png" id="smile" style="background:none;" onclick="add_smile(':sm25:')">
                        <img src="/assets/img/smiles/smile (26).png" id="smile" style="background:none;" onclick="add_smile(':sm26:')">
                        <img src="/assets/img/smiles/smile (27).png" id="smile" style="background:none;" onclick="add_smile(':sm27:')">
                        <img src="/assets/img/smiles/smile (28).png" id="smile" style="background:none;" onclick="add_smile(':sm28:')">
                        <img src="/assets/img/smiles/smile (29).png" id="smile" style="background:none;" onclick="add_smile(':sm29:')">
                        <img src="/assets/img/smiles/smile (30).png" id="smile" style="background:none;" onclick="add_smile(':sm30:')">
                        <img src="/assets/img/smiles/smile (31).png" id="smile" style="background:none;" onclick="add_smile(':sm31:')">
                        <img src="/assets/img/smiles/smile (32).png" id="smile" style="background:none;" onclick="add_smile(':sm32:')">
                        <img src="/assets/img/smiles/smile (33).png" id="smile" style="background:none;" onclick="add_smile(':sm33:')">
                        <img src="/assets/img/smiles/smile (34).png" id="smile" style="background:none;" onclick="add_smile(':sm34:')">
                        <img src="/assets/img/smiles/smile (35).png" id="smile" style="background:none;" onclick="add_smile(':sm35:')">
                        <img src="/assets/img/smiles/smile (36).png" id="smile" style="background:none;" onclick="add_smile(':sm36:')">
                        <img src="/assets/img/smiles/smile (37).png" id="smile" style="background:none;" onclick="add_smile(':sm37:')">
                        <img src="/assets/img/smiles/smile (38).png" id="smile" style="background:none;" onclick="add_smile(':sm38:')">
                        <img src="/assets/img/smiles/smile (39).png" id="smile" style="background:none;" onclick="add_smile(':sm39:')">
                        <img src="/assets/img/smiles/smile (40).png" id="smile" style="background:none;" onclick="add_smile(':sm40:')">
                        <img src="/assets/img/smiles/smile (41).png" id="smile" style="background:none;" onclick="add_smile(':sm41:')">
                        <img src="/assets/img/smiles/smile (42).png" id="smile" style="background:none;" onclick="add_smile(':sm42:')">
                        <img src="/assets/img/smiles/smile (43).png" id="smile" style="background:none;" onclick="add_smile(':sm43:')">
                        <img src="/assets/img/smiles/smile (44).png" id="smile" style="background:none;" onclick="add_smile(':sm44:')">
                        <img src="/assets/img/smiles/smile (45).png" id="smile" style="background:none;" onclick="add_smile(':sm45:')">
                        <img src="/assets/img/smiles/smile (46).png" id="smile" style="background:none;" onclick="add_smile(':sm46:')">
                        <img src="/assets/img/smiles/smile (47).png" id="smile" style="background:none;" onclick="add_smile(':sm47:')">
                        <img src="/assets/img/smiles/smile (48).png" id="smile" style="background:none;" onclick="add_smile(':sm48:')">
                        <img src="/assets/img/smiles/smile (49).png" id="smile" style="background:none;" onclick="add_smile(':sm49:')">
                        <img src="/assets/img/smiles/smile (50).png" id="smile" style="background:none;" onclick="add_smile(':sm50:')">
                        <img src="/assets/img/smiles/smile (51).png" id="smile" style="background:none;" onclick="add_smile(':sm51:')">
                        <img src="/assets/img/smiles/smile (52).png" id="smile" style="background:none;" onclick="add_smile(':sm52:')">
                        <img src="/assets/img/smiles/smile (53).png" id="smile" style="background:none;" onclick="add_smile(':sm53:')">
                        <img src="/assets/img/smiles/smile (54).png" id="smile" style="background:none;" onclick="add_smile(':sm54:')">
                        <img src="/assets/img/smiles/smile (55).png" id="smile" style="background:none;" onclick="add_smile(':sm55:')">
                        <img src="/assets/img/smiles/smile (56).png" id="smile" style="background:none;" onclick="add_smile(':sm56:')">
                        <img src="/assets/img/smiles/smile (57).png" id="smile" style="background:none;" onclick="add_smile(':sm57:')">
                        <img src="/assets/img/smiles/smile (58).png" id="smile" style="background:none;" onclick="add_smile(':sm58:')">
                        <img src="/assets/img/smiles/smile (59).png" id="smile" style="background:none;" onclick="add_smile(':sm59:')">
                        <img src="/assets/img/smiles/smile (60).png" id="smile" style="background:none;" onclick="add_smile(':sm60:')">
                        <img src="/assets/img/smiles/smile (61).png" id="smile" style="background:none;" onclick="add_smile(':sm61:')">
                        <img src="/assets/img/smiles/smile (62).png" id="smile" style="background:none;" onclick="add_smile(':sm62:')">
                        <img src="/assets/img/smiles/smile (63).png" id="smile" style="background:none;" onclick="add_smile(':sm63:')">
                        <img src="/assets/img/smiles/smile (64).png" id="smile" style="background:none;" onclick="add_smile(':sm64:')">
                        <img src="/assets/img/smiles/smile (65).png" id="smile" style="background:none;" onclick="add_smile(':sm65:')">
                        <img src="/assets/img/smiles/smile (66).png" id="smile" style="background:none;" onclick="add_smile(':sm66:')">
                        <img src="/assets/img/smiles/smile (67).png" id="smile" style="background:none;" onclick="add_smile(':sm67:')">
                        <img src="/assets/img/smiles/smile (68).png" id="smile" style="background:none;" onclick="add_smile(':sm68:')">
                        <img src="/assets/img/smiles/smile (69).png" id="smile" style="background:none;" onclick="add_smile(':sm69:')">
                        <img src="/assets/img/smiles/smile (70).png" id="smile" style="background:none;" onclick="add_smile(':sm70:')">
                        <img src="/assets/img/smiles/smile (71).png" id="smile" style="background:none;" onclick="add_smile(':sm71:')">
                        <img src="/assets/img/smiles/smile (72).png" id="smile" style="background:none;" onclick="add_smile(':sm72:')">
                        <img src="/assets/img/smiles/smile (73).png" id="smile" style="background:none;" onclick="add_smile(':sm73:')">
                        <img src="/assets/img/smiles/smile (74).png" id="smile" style="background:none;" onclick="add_smile(':sm74:')">
                        <img src="/assets/img/smiles/smile (75).png" id="smile" style="background:none;" onclick="add_smile(':sm75:')">
                        <img src="/assets/img/smiles/smile (76).png" id="smile" style="background:none;" onclick="add_smile(':sm76:')">
                        <img src="/assets/img/smiles/smile (77).png" id="smile" style="background:none;" onclick="add_smile(':sm77:')">
                        <img src="/assets/img/smiles/smile (78).png" id="smile" style="background:none;" onclick="add_smile(':sm78:')">
                        <img src="/assets/img/smiles/smile (79).png" id="smile" style="background:none;" onclick="add_smile(':sm79:')">
                        <img src="/assets/img/smiles/smile (80).png" id="smile" style="background:none;" onclick="add_smile(':sm80:')">
                        <img src="/assets/img/smiles/smile (81).png" id="smile" style="background:none;" onclick="add_smile(':sm81:')">
                        <img src="/assets/img/smiles/smile (82).png" id="smile" style="background:none;" onclick="add_smile(':sm82:')">
                        <img src="/assets/img/smiles/smile (83).png" id="smile" style="background:none;" onclick="add_smile(':sm83:')">
                        <img src="/assets/img/smiles/smile (84).png" id="smile" style="background:none;" onclick="add_smile(':sm84:')">
                        <img src="/assets/img/smiles/smile (85).png" id="smile" style="background:none;" onclick="add_smile(':sm85:')">
                        <img src="/assets/img/smiles/smile (86).png" id="smile" style="background:none;" onclick="add_smile(':sm86:')">
                        <img src="/assets/img/smiles/smile (87).png" id="smile" style="background:none;" onclick="add_smile(':sm87:')">
                        <img src="/assets/img/smiles/smile (88).png" id="smile" style="background:none;" onclick="add_smile(':sm88:')">
                        <img src="/assets/img/smiles/smile (89).png" id="smile" style="background:none;" onclick="add_smile(':sm89:')">
                        <img src="/assets/img/smiles/smile (90).png" id="smile" style="background:none;" onclick="add_smile(':sm90:')">
                        <img src="/assets/img/smiles/smile (91).png" id="smile" style="background:none;" onclick="add_smile(':sm91:')">
                        <img src="/assets/img/smiles/smile (92).png" id="smile" style="background:none;" onclick="add_smile(':sm92:')">
                        <img src="/assets/img/smiles/smile (93).png" id="smile" style="background:none;" onclick="add_smile(':sm93:')">
                        <img src="/assets/img/smiles/smile (94).png" id="smile" style="background:none;" onclick="add_smile(':sm94:')">
                        <img src="/assets/img/smiles/smile (95).png" id="smile" style="background:none;" onclick="add_smile(':sm95:')">
                        <img src="/assets/img/smiles/smile (96).png" id="smile" style="background:none;" onclick="add_smile(':sm96:')">
                        <img src="/assets/img/smiles/smile (97).png" id="smile" style="background:none;" onclick="add_smile(':sm97:')">
                        <img src="/assets/img/smiles/smile (98).png" id="smile" style="background:none;" onclick="add_smile(':sm98:')">
                        <img src="/assets/img/smiles/smile (99).png" id="smile" style="background:none;" onclick="add_smile(':sm99:')">
                        <img src="/assets/img/smiles/smile (100).png" id="smile" style="background:none;" onclick="add_smile(':sm100:')">
                        <img src="/assets/img/smiles/smile (101).png" id="smile" style="background:none;" onclick="add_smile(':sm101:')">
                        <img src="/assets/img/smiles/smile (102).png" id="smile" style="background:none;" onclick="add_smile(':sm102:')">
                        <img src="/assets/img/smiles/smile (103).png" id="smile" style="background:none;" onclick="add_smile(':sm103:')">
                        <img src="/assets/img/smiles/smile (104).png" id="smile" style="background:none;" onclick="add_smile(':sm104:')">
                        <img src="/assets/img/smiles/smile (105).png" id="smile" style="background:none;" onclick="add_smile(':sm105:')">
                        <img src="/assets/img/smiles/smile (106).png" id="smile" style="background:none;" onclick="add_smile(':sm106:')">
                        <img src="/assets/img/smiles/smile (107).png" id="smile" style="background:none;" onclick="add_smile(':sm107:')">
                        <img src="/assets/img/smiles/smile (108).png" id="smile" style="background:none;" onclick="add_smile(':sm108:')">
                        <img src="/assets/img/smiles/smile (109).png" id="smile" style="background:none;" onclick="add_smile(':sm109:')">
                        <img src="/assets/img/smiles/smile (110).png" id="smile" style="background:none;" onclick="add_smile(':sm110:')">
                        <img src="/assets/img/smiles/smile (111).png" id="smile" style="background:none;" onclick="add_smile(':sm111:')">
                        <img src="/assets/img/smiles/smile (112).png" id="smile" style="background:none;" onclick="add_smile(':sm112:')">
                        <img src="/assets/img/smiles/smile (113).png" id="smile" style="background:none;" onclick="add_smile(':sm113:')">
                        <img src="/assets/img/smiles/smile (114).png" id="smile" style="background:none;" onclick="add_smile(':sm114:')">
                        <img src="/assets/img/smiles/smile (115).png" id="smile" style="background:none;" onclick="add_smile(':sm115:')">
                        <img src="/assets/img/smiles/smile (116).png" id="smile" style="background:none;" onclick="add_smile(':sm116:')">
                        <img src="/assets/img/smiles/smile (117).png" id="smile" style="background:none;" onclick="add_smile(':sm117:')">
                        <img src="/assets/img/smiles/smile (118).png" id="smile" style="background:none;" onclick="add_smile(':sm118:')">
                        <img src="/assets/img/smiles/smile (119).png" id="smile" style="background:none;" onclick="add_smile(':sm119:')">
                        <img src="/assets/img/smiles/smile (120).png" id="smile" style="background:none;" onclick="add_smile(':sm120:')">
                        <img src="/assets/img/smiles/smile (121).png" id="smile" style="background:none;" onclick="add_smile(':sm121:')">
                        <img src="/assets/img/smiles/smile (122).png" id="smile" style="background:none;" onclick="add_smile(':sm122:')">
                        <img src="/assets/img/smiles/smile (123).png" id="smile" style="background:none;" onclick="add_smile(':sm123:')">
                        <img src="/assets/img/smiles/smile (124).png" id="smile" style="background:none;" onclick="add_smile(':sm124:')">
                        <img src="/assets/img/smiles/smile (125).png" id="smile" style="background:none;" onclick="add_smile(':sm125:')">
                        <img src="/assets/img/smiles/smile (126).png" id="smile" style="background:none;" onclick="add_smile(':sm126:')">
                        <img src="/assets/img/smiles/smile (127).png" id="smile" style="background:none;" onclick="add_smile(':sm127:')">
                        <img src="/assets/img/smiles/smile (128).png" id="smile" style="background:none;" onclick="add_smile(':sm128:')">
                        <img src="/assets/img/smiles/smile (129).png" id="smile" style="background:none;" onclick="add_smile(':sm129:')">
                        <img src="/assets/img/smiles/smile (130).png" id="smile" style="background:none;" onclick="add_smile(':sm130:')">
                        <img src="/assets/img/smiles/smile (131).png" id="smile" style="background:none;" onclick="add_smile(':sm131:')">
                        <img src="/assets/img/smiles/smile (132).png" id="smile" style="background:none;" onclick="add_smile(':sm132:')">
                        <img src="/assets/img/smiles/smile (133).png" id="smile" style="background:none;" onclick="add_smile(':sm133:')">
                        <img src="/assets/img/smiles/smile (134).png" id="smile" style="background:none;" onclick="add_smile(':sm134:')">
                        <img src="/assets/img/smiles/smile (135).png" id="smile" style="background:none;" onclick="add_smile(':sm135:')">
                        <img src="/assets/img/smiles/smile (136).png" id="smile" style="background:none;" onclick="add_smile(':sm136:')">
                        <img src="/assets/img/smiles/smile (137).png" id="smile" style="background:none;" onclick="add_smile(':sm137:')">
                        <img src="/assets/img/smiles/smile (138).png" id="smile" style="background:none;" onclick="add_smile(':sm138:')">
                        <img src="/assets/img/smiles/smile (139).png" id="smile" style="background:none;" onclick="add_smile(':sm139:')">
                        <img src="/assets/img/smiles/smile (140).png" id="smile" style="background:none;" onclick="add_smile(':sm140:')">
                        <img src="/assets/img/smiles/smile (141).png" id="smile" style="background:none;" onclick="add_smile(':sm141:')">
                        <img src="/assets/img/smiles/smile (142).png" id="smile" style="background:none;" onclick="add_smile(':sm142:')">
                        <img src="/assets/img/smiles/smile (143).png" id="smile" style="background:none;" onclick="add_smile(':sm143:')">
                        <img src="/assets/img/smiles/smile (144).png" id="smile" style="background:none;" onclick="add_smile(':sm144:')">
                        <img src="/assets/img/smiles/smile (145).png" id="smile" style="background:none;" onclick="add_smile(':sm145:')">
                        <img src="/assets/img/smiles/smile (146).png" id="smile" style="background:none;" onclick="add_smile(':sm146:')">
                        <img src="/assets/img/smiles/smile (147).png" id="smile" style="background:none;" onclick="add_smile(':sm147:')">
                        <img src="/assets/img/smiles/smile (148).png" id="smile" style="background:none;" onclick="add_smile(':sm148:')">
                        <img src="/assets/img/smiles/smile (149).png" id="smile" style="background:none;" onclick="add_smile(':sm149:')">
                        <img src="/assets/img/smiles/smile (150).png" id="smile" style="background:none;" onclick="add_smile(':sm150:')">
                        <img src="/assets/img/smiles/smile (151).png" id="smile" style="background:none;" onclick="add_smile(':sm151:')">
                        <img src="/assets/img/smiles/smile (152).png" id="smile" style="background:none;" onclick="add_smile(':sm152:')">
                        <img src="/assets/img/smiles/smile (153).png" id="smile" style="background:none;" onclick="add_smile(':sm153:')">
                        <img src="/assets/img/smiles/smile (154).png" id="smile" style="background:none;" onclick="add_smile(':sm154:')">
                        <img src="/assets/img/smiles/smile (155).png" id="smile" style="background:none;" onclick="add_smile(':sm155:')">
                        <img src="/assets/img/smiles/smile (156).png" id="smile" style="background:none;" onclick="add_smile(':sm156:')">
                        <img src="/assets/img/smiles/smile (157).png" id="smile" style="background:none;" onclick="add_smile(':sm157:')">
                        <img src="/assets/img/smiles/smile (158).png" id="smile" style="background:none;" onclick="add_smile(':sm158:')">
                        <img src="/assets/img/smiles/smile (159).png" id="smile" style="background:none;" onclick="add_smile(':sm159:')">
                        <img src="/assets/img/smiles/smile (160).png" id="smile" style="background:none;" onclick="add_smile(':sm160:')">
                        <img src="/assets/img/smiles/smile (161).png" id="smile" style="background:none;" onclick="add_smile(':sm161:')">
                        <img src="/assets/img/smiles/smile (162).png" id="smile" style="background:none;" onclick="add_smile(':sm162:')">
                        <img src="/assets/img/smiles/smile (163).png" id="smile" style="background:none;" onclick="add_smile(':sm163:')">
                        <img src="/assets/img/smiles/smile (164).png" id="smile" style="background:none;" onclick="add_smile(':sm164:')">
                        <img src="/assets/img/smiles/smile (165).png" id="smile" style="background:none;" onclick="add_smile(':sm165:')">
                        <img src="/assets/img/smiles/smile (166).png" id="smile" style="background:none;" onclick="add_smile(':sm166:')">
                        <img src="/assets/img/smiles/smile (167).png" id="smile" style="background:none;" onclick="add_smile(':sm167:')">
                        <img src="/assets/img/smiles/smile (168).png" id="smile" style="background:none;" onclick="add_smile(':sm168:')">
                        <img src="/assets/img/smiles/smile (169).png" id="smile" style="background:none;" onclick="add_smile(':sm169:')">
                        <img src="/assets/img/smiles/smile (170).png" id="smile" style="background:none;" onclick="add_smile(':sm170:')">
                        <img src="/assets/img/smiles/smile (171).png" id="smile" style="background:none;" onclick="add_smile(':sm171:')">
                        <img src="/assets/img/smiles/smile (172).png" id="smile" style="background:none;" onclick="add_smile(':sm172:')">
                        <img src="/assets/img/smiles/smile (173).png" id="smile" style="background:none;" onclick="add_smile(':sm173:')">
                        <img src="/assets/img/smiles/smile (174).png" id="smile" style="background:none;" onclick="add_smile(':sm174:')">
                        <img src="/assets/img/smiles/smile (175).png" id="smile" style="background:none;" onclick="add_smile(':sm175:')">
                        <img src="/assets/img/smiles/smile (176).png" id="smile" style="background:none;" onclick="add_smile(':sm176:')">
                        <img src="/assets/img/smiles/smile (177).png" id="smile" style="background:none;" onclick="add_smile(':sm177:')">
                        <img src="/assets/img/smiles/smile (178).png" id="smile" style="background:none;" onclick="add_smile(':sm178:')">
                        <img src="/assets/img/smiles/smile (179).png" id="smile" style="background:none;" onclick="add_smile(':sm179:')">
                        <img src="/assets/img/smiles/smile (180).png" id="smile" style="background:none;" onclick="add_smile(':sm180:')">
                        <img src="/assets/img/smiles/smile (181).png" id="smile" style="background:none;" onclick="add_smile(':sm181:')">
                        <img src="/assets/img/smiles/smile (182).png" id="smile" style="background:none;" onclick="add_smile(':sm182:')">
                        <img src="/assets/img/smiles/smile (183).png" id="smile" style="background:none;" onclick="add_smile(':sm183:')">
                        <img src="/assets/img/smiles/smile (184).png" id="smile" style="background:none;" onclick="add_smile(':sm184:')">
                        <img src="/assets/img/smiles/smile (185).png" id="smile" style="background:none;" onclick="add_smile(':sm185:')">
                        <img src="/assets/img/smiles/smile (186).png" id="smile" style="background:none;" onclick="add_smile(':sm186:')">
                        <img src="/assets/img/smiles/smile (187).png" id="smile" style="background:none;" onclick="add_smile(':sm187:')">
                        <img src="/assets/img/smiles/smile (188).png" id="smile" style="background:none;" onclick="add_smile(':sm188:')">
                        <img src="/assets/img/smiles/smile (189).png" id="smile" style="background:none;" onclick="add_smile(':sm189:')">
                        <img src="/assets/img/smiles/smile (190).png" id="smile" style="background:none;" onclick="add_smile(':sm190:')">
                        <img src="/assets/img/smiles/smile (191).png" id="smile" style="background:none;" onclick="add_smile(':sm191:')">
                        <img src="/assets/img/smiles/smile (192).png" id="smile" style="background:none;" onclick="add_smile(':sm192:')">
                        <img src="/assets/img/smiles/smile (193).png" id="smile" style="background:none;" onclick="add_smile(':sm193:')">
                        <img src="/assets/img/smiles/smile (194).png" id="smile" style="background:none;" onclick="add_smile(':sm194:')">
                        <img src="/assets/img/smiles/smile (195).png" id="smile" style="background:none;" onclick="add_smile(':sm195:')">
                        <img src="/assets/img/smiles/smile (196).png" id="smile" style="background:none;" onclick="add_smile(':sm196:')">
                        <img src="/assets/img/smiles/smile (197).png" id="smile" style="background:none;" onclick="add_smile(':sm197:')">
                        <img src="/assets/img/smiles/smile (198).png" id="smile" style="background:none;" onclick="add_smile(':sm198:')">
                        <img src="/assets/img/smiles/smile (199).png" id="smile" style="background:none;" onclick="add_smile(':sm199:')">
                        <img src="/assets/img/smiles/smile (200).png" id="smile" style="background:none;" onclick="add_smile(':sm200:')">
                        <img src="/assets/img/smiles/smile (201).png" id="smile" style="background:none;" onclick="add_smile(':sm201:')">
                        <img src="/assets/img/smiles/smile (202).png" id="smile" style="background:none;" onclick="add_smile(':sm202:')">
                        <img src="/assets/img/smiles/smile (203).png" id="smile" style="background:none;" onclick="add_smile(':sm203:')">
                        <img src="/assets/img/smiles/smile (204).png" id="smile" style="background:none;" onclick="add_smile(':sm204:')">
                        <img src="/assets/img/smiles/smile (205).png" id="smile" style="background:none;" onclick="add_smile(':sm205:')">
                        <img src="/assets/img/smiles/smile (206).png" id="smile" style="background:none;" onclick="add_smile(':sm206:')">
                        <img src="/assets/img/smiles/smile (207).png" id="smile" style="background:none;" onclick="add_smile(':sm207:')">
                        <img src="/assets/img/smiles/smile (208).png" id="smile" style="background:none;" onclick="add_smile(':sm208:')">
                        <img src="/assets/img/smiles/smile (209).png" id="smile" style="background:none;" onclick="add_smile(':sm209:')">
                        <img src="/assets/img/smiles/smile (210).png" id="smile" style="background:none;" onclick="add_smile(':sm210:')">
                        <img src="/assets/img/smiles/smile (211).png" id="smile" style="background:none;" onclick="add_smile(':sm211:')">
                        <img src="/assets/img/smiles/smile (212).png" id="smile" style="background:none;" onclick="add_smile(':sm212:')">
                        <img src="/assets/img/smiles/smile (213).png" id="smile" style="background:none;" onclick="add_smile(':sm213:')">
                        <img src="/assets/img/smiles/smile (214).png" id="smile" style="background:none;" onclick="add_smile(':sm214:')">
                        <img src="/assets/img/smiles/smile (215).png" id="smile" style="background:none;" onclick="add_smile(':sm215:')">
                        <img src="/assets/img/smiles/smile (216).png" id="smile" style="background:none;" onclick="add_smile(':sm216:')">
                        <img src="/assets/img/smiles/smile (217).png" id="smile" style="background:none;" onclick="add_smile(':sm217:')">
                        <img src="/assets/img/smiles/smile (218).png" id="smile" style="background:none;" onclick="add_smile(':sm218:')">
                        <img src="/assets/img/smiles/smile (219).png" id="smile" style="background:none;" onclick="add_smile(':sm219:')">
                        <img src="/assets/img/smiles/smile (220).png" id="smile" style="background:none;" onclick="add_smile(':sm220:')">
                        <img src="/assets/img/smiles/smile (221).png" id="smile" style="background:none;" onclick="add_smile(':sm221:')">
                        <img src="/assets/img/smiles/smile (222).png" id="smile" style="background:none;" onclick="add_smile(':sm222:')">
                        <img src="/assets/img/smiles/smile (223).png" id="smile" style="background:none;" onclick="add_smile(':sm223:')">
                        <img src="/assets/img/smiles/smile (224).png" id="smile" style="background:none;" onclick="add_smile(':sm224:')">
                        <img src="/assets/img/smiles/smile (225).png" id="smile" style="background:none;" onclick="add_smile(':sm225:')">
                        <img src="/assets/img/smiles/smile (226).png" id="smile" style="background:none;" onclick="add_smile(':sm226:')">
                        <img src="/assets/img/smiles/smile (227).png" id="smile" style="background:none;" onclick="add_smile(':sm227:')">
                        <img src="/assets/img/smiles/smile (228).png" id="smile" style="background:none;" onclick="add_smile(':sm228:')">
                        <img src="/assets/img/smiles/smile (229).png" id="smile" style="background:none;" onclick="add_smile(':sm229:')">
                        <img src="/assets/img/smiles/smile (230).png" id="smile" style="background:none;" onclick="add_smile(':sm230:')">
                        <img src="/assets/img/smiles/smile (231).png" id="smile" style="background:none;" onclick="add_smile(':sm231:')">
                        <img src="/assets/img/smiles/smile (232).png" id="smile" style="background:none;" onclick="add_smile(':sm232:')">
                        <img src="/assets/img/smiles/smile (233).png" id="smile" style="background:none;" onclick="add_smile(':sm233:')">
                        <img src="/assets/img/smiles/smile (234).png" id="smile" style="background:none;" onclick="add_smile(':sm234:')">
                        <img src="/assets/img/smiles/smile (235).png" id="smile" style="background:none;" onclick="add_smile(':sm235:')">
                        <img src="/assets/img/smiles/smile (236).png" id="smile" style="background:none;" onclick="add_smile(':sm236:')">
                        <img src="/assets/img/smiles/smile (237).png" id="smile" style="background:none;" onclick="add_smile(':sm237:')">
                        <img src="/assets/img/smiles/smile (238).png" id="smile" style="background:none;" onclick="add_smile(':sm238:')">
                        <img src="/assets/img/smiles/smile (239).png" id="smile" style="background:none;" onclick="add_smile(':sm239:')">
                        <img src="/assets/img/smiles/smile (240).png" id="smile" style="background:none;" onclick="add_smile(':sm240:')">
                        <img src="/assets/img/smiles/smile (241).png" id="smile" style="background:none;" onclick="add_smile(':sm241:')">
                        <img src="/assets/img/smiles/smile (242).png" id="smile" style="background:none;" onclick="add_smile(':sm242:')">
                        <img src="/assets/img/smiles/smile (243).png" id="smile" style="background:none;" onclick="add_smile(':sm243:')">
                        <img src="/assets/img/smiles/smile (244).png" id="smile" style="background:none;" onclick="add_smile(':sm244:')">
                        <img src="/assets/img/smiles/smile (245).png" id="smile" style="background:none;" onclick="add_smile(':sm245:')">
                        <img src="/assets/img/smiles/smile (246).png" id="smile" style="background:none;" onclick="add_smile(':sm246:')">
                        <img src="/assets/img/smiles/smile (247).png" id="smile" style="background:none;" onclick="add_smile(':sm247:')">
                        <img src="/assets/img/smiles/smile (248).png" id="smile" style="background:none;" onclick="add_smile(':sm248:')">
                        <img src="/assets/img/smiles/smile (249).png" id="smile" style="background:none;" onclick="add_smile(':sm249:')">
                        <img src="/assets/img/smiles/smile (250).png" id="smile" style="background:none;" onclick="add_smile(':sm250:')">
                        <img src="/assets/img/smiles/smile (251).png" id="smile" style="background:none;" onclick="add_smile(':sm251:')">
                        <img src="/assets/img/smiles/smile (252).png" id="smile" style="background:none;" onclick="add_smile(':sm252:')">
                        <img src="/assets/img/smiles/smile (253).png" id="smile" style="background:none;" onclick="add_smile(':sm253:')">
                        <img src="/assets/img/smiles/smile (254).png" id="smile" style="background:none;" onclick="add_smile(':sm254:')">
                        <img src="/assets/img/smiles/smile (255).png" id="smile" style="background:none;" onclick="add_smile(':sm255:')">
                        <img src="/assets/img/smiles/smile (256).png" id="smile" style="background:none;" onclick="add_smile(':sm256:')">
                        <img src="/assets/img/smiles/smile (257).png" id="smile" style="background:none;" onclick="add_smile(':sm257:')">
                        <img src="/assets/img/smiles/smile (258).png" id="smile" style="background:none;" onclick="add_smile(':sm258:')">
                        <img src="/assets/img/smiles/smile (259).png" id="smile" style="background:none;" onclick="add_smile(':sm259:')">
                        <img src="/assets/img/smiles/smile (260).png" id="smile" style="background:none;" onclick="add_smile(':sm260:')">
                        <img src="/assets/img/smiles/smile (261).png" id="smile" style="background:none;" onclick="add_smile(':sm261:')">
                        <img src="/assets/img/smiles/smile (262).png" id="smile" style="background:none;" onclick="add_smile(':sm262:')">
                        <img src="/assets/img/smiles/smile (263).png" id="smile" style="background:none;" onclick="add_smile(':sm263:')">
                        <img src="/assets/img/smiles/smile (264).png" id="smile" style="background:none;" onclick="add_smile(':sm264:')">
                        <img src="/assets/img/smiles/smile (265).png" id="smile" style="background:none;" onclick="add_smile(':sm265:')">
                        <img src="/assets/img/smiles/smile (266).png" id="smile" style="background:none;" onclick="add_smile(':sm266:')">
                        <img src="/assets/img/smiles/smile (267).png" id="smile" style="background:none;" onclick="add_smile(':sm267:')">
                        <img src="/assets/img/smiles/smile (268).png" id="smile" style="background:none;" onclick="add_smile(':sm268:')">
                        <img src="/assets/img/smiles/smile (269).png" id="smile" style="background:none;" onclick="add_smile(':sm269:')">
                        <img src="/assets/img/smiles/smile (270).png" id="smile" style="background:none;" onclick="add_smile(':sm270:')">
                        <img src="/assets/img/smiles/smile (271).png" id="smile" style="background:none;" onclick="add_smile(':sm271:')">
                        <img src="/assets/img/smiles/smile (272).png" id="smile" style="background:none;" onclick="add_smile(':sm272:')">
                        <img src="/assets/img/smiles/smile (273).png" id="smile" style="background:none;" onclick="add_smile(':sm273:')">
                        <img src="/assets/img/smiles/smile (274).png" id="smile" style="background:none;" onclick="add_smile(':sm274:')">
                        <img src="/assets/img/smiles/smile (275).png" id="smile" style="background:none;" onclick="add_smile(':sm275:')">
                        <img src="/assets/img/smiles/smile (276).png" id="smile" style="background:none;" onclick="add_smile(':sm276:')">
                        <img src="/assets/img/smiles/smile (277).png" id="smile" style="background:none;" onclick="add_smile(':sm277:')">
                        <img src="/assets/img/smiles/smile (278).png" id="smile" style="background:none;" onclick="add_smile(':sm278:')">
                        <img src="/assets/img/smiles/smile (279).png" id="smile" style="background:none;" onclick="add_smile(':sm279:')">
                        <img src="/assets/img/smiles/smile (280).png" id="smile" style="background:none;" onclick="add_smile(':sm280:')">
                        <img src="/assets/img/smiles/smile (281).png" id="smile" style="background:none;" onclick="add_smile(':sm281:')">
                        <img src="/assets/img/smiles/smile (282).png" id="smile" style="background:none;" onclick="add_smile(':sm282:')">
                        <img src="/assets/img/smiles/smile (283).png" id="smile" style="background:none;" onclick="add_smile(':sm283:')">
                        <img src="/assets/img/smiles/smile (284).png" id="smile" style="background:none;" onclick="add_smile(':sm284:')">
                        <img src="/assets/img/smiles/smile (285).png" id="smile" style="background:none;" onclick="add_smile(':sm285:')">
                        <img src="/assets/img/smiles/smile (286).png" id="smile" style="background:none;" onclick="add_smile(':sm286:')">
                        <img src="/assets/img/smiles/smile (287).png" id="smile" style="background:none;" onclick="add_smile(':sm287:')">
                        <img src="/assets/img/smiles/smile (288).png" id="smile" style="background:none;" onclick="add_smile(':sm288:')">
                        <img src="/assets/img/smiles/smile (289).png" id="smile" style="background:none;" onclick="add_smile(':sm289:')">
                        <img src="/assets/img/smiles/smile (290).png" id="smile" style="background:none;" onclick="add_smile(':sm290:')">
                        <img src="/assets/img/smiles/smile (291).png" id="smile" style="background:none;" onclick="add_smile(':sm291:')">
                        <img src="/assets/img/smiles/smile (292).png" id="smile" style="background:none;" onclick="add_smile(':sm292:')">
                        <img src="/assets/img/smiles/smile (293).png" id="smile" style="background:none;" onclick="add_smile(':sm293:')">
                        <img src="/assets/img/smiles/smile (294).png" id="smile" style="background:none;" onclick="add_smile(':sm294:')">
                        <img src="/assets/img/smiles/smile (295).png" id="smile" style="background:none;" onclick="add_smile(':sm295:')">
                        <img src="/assets/img/smiles/smile (296).png" id="smile" style="background:none;" onclick="add_smile(':sm296:')">
                        <img src="/assets/img/smiles/smile (297).png" id="smile" style="background:none;" onclick="add_smile(':sm297:')">
                        <img src="/assets/img/smiles/smile (298).png" id="smile" style="background:none;" onclick="add_smile(':sm298:')">
                        <img src="/assets/img/smiles/smile (299).png" id="smile" style="background:none;" onclick="add_smile(':sm299:')">
                        <img src="/assets/img/smiles/smile (300).png" id="smile" style="background:none;" onclick="add_smile(':sm300:')">
                        <img src="/assets/img/smiles/smile (301).png" id="smile" style="background:none;" onclick="add_smile(':sm301:')">
                        <img src="/assets/img/smiles/smile (302).png" id="smile" style="background:none;" onclick="add_smile(':sm302:')">
                        <img src="/assets/img/smiles/smile (303).png" id="smile" style="background:none;" onclick="add_smile(':sm303:')">
                        <img src="/assets/img/smiles/smile (304).png" id="smile" style="background:none;" onclick="add_smile(':sm304:')">
                        <img src="/assets/img/smiles/smile (305).png" id="smile" style="background:none;" onclick="add_smile(':sm305:')">
                        <img src="/assets/img/smiles/smile (306).png" id="smile" style="background:none;" onclick="add_smile(':sm306:')">
                        <img src="/assets/img/smiles/smile (307).png" id="smile" style="background:none;" onclick="add_smile(':sm307:')">
                        <img src="/assets/img/smiles/smile (308).png" id="smile" style="background:none;" onclick="add_smile(':sm308:')">
                        <img src="/assets/img/smiles/smile (309).png" id="smile" style="background:none;" onclick="add_smile(':sm309:')">
                        <img src="/assets/img/smiles/smile (310).png" id="smile" style="background:none;" onclick="add_smile(':sm310:')">
                        <img src="/assets/img/smiles/smile (311).png" id="smile" style="background:none;" onclick="add_smile(':sm311:')">
                        <img src="/assets/img/smiles/smile (312).png" id="smile" style="background:none;" onclick="add_smile(':sm312:')">
                        <img src="/assets/img/smiles/smile (313).png" id="smile" style="background:none;" onclick="add_smile(':sm313:')">
                        <img src="/assets/img/smiles/smile (314).png" id="smile" style="background:none;" onclick="add_smile(':sm314:')">
                        <img src="/assets/img/smiles/smile (315).png" id="smile" style="background:none;" onclick="add_smile(':sm315:')">
                        <img src="/assets/img/smiles/smile (316).png" id="smile" style="background:none;" onclick="add_smile(':sm316:')">
                        <img src="/assets/img/smiles/smile (317).png" id="smile" style="background:none;" onclick="add_smile(':sm317:')">
                        <img src="/assets/img/smiles/smile (318).png" id="smile" style="background:none;" onclick="add_smile(':sm318:')">
                        <img src="/assets/img/smiles/smile (319).png" id="smile" style="background:none;" onclick="add_smile(':sm319:')">
                        <img src="/assets/img/smiles/smile (320).png" id="smile" style="background:none;" onclick="add_smile(':sm320:')">
                        <img src="/assets/img/smiles/smile (321).png" id="smile" style="background:none;" onclick="add_smile(':sm321:')">
                        <img src="/assets/img/smiles/smile (322).png" id="smile" style="background:none;" onclick="add_smile(':sm322:')">
                        <img src="/assets/img/smiles/smile (323).png" id="smile" style="background:none;" onclick="add_smile(':sm323:')">
                        <img src="/assets/img/smiles/smile (324).png" id="smile" style="background:none;" onclick="add_smile(':sm324:')">
                        <img src="/assets/img/smiles/smile (325).png" id="smile" style="background:none;" onclick="add_smile(':sm325:')">
                        <img src="/assets/img/smiles/smile (326).png" id="smile" style="background:none;" onclick="add_smile(':sm326:')">
                        <img src="/assets/img/smiles/smile (327).png" id="smile" style="background:none;" onclick="add_smile(':sm327:')">
                        <img src="/assets/img/smiles/smile (328).png" id="smile" style="background:none;" onclick="add_smile(':sm328:')">
                        <img src="/assets/img/smiles/smile (329).png" id="smile" style="background:none;" onclick="add_smile(':sm329:')">
                        <img src="/assets/img/smiles/smile (330).png" id="smile" style="background:none;" onclick="add_smile(':sm330:')">
                        <img src="/assets/img/smiles/smile (331).png" id="smile" style="background:none;" onclick="add_smile(':sm331:')">
                        <img src="/assets/img/smiles/smile (332).png" id="smile" style="background:none;" onclick="add_smile(':sm332:')">
                        <img src="/assets/img/smiles/smile (333).png" id="smile" style="background:none;" onclick="add_smile(':sm333:')">
                        <img src="/assets/img/smiles/smile (334).png" id="smile" style="background:none;" onclick="add_smile(':sm334:')">
                        <img src="/assets/img/smiles/smile (335).png" id="smile" style="background:none;" onclick="add_smile(':sm335:')">
                        <img src="/assets/img/smiles/smile (336).png" id="smile" style="background:none;" onclick="add_smile(':sm336:')">
                        <img src="/assets/img/smiles/smile (337).png" id="smile" style="background:none;" onclick="add_smile(':sm337:')">
                        <img src="/assets/img/smiles/smile (338).png" id="smile" style="background:none;" onclick="add_smile(':sm338:')">
                        <img src="/assets/img/smiles/smile (339).png" id="smile" style="background:none;" onclick="add_smile(':sm339:')">
                        <img src="/assets/img/smiles/smile (340).png" id="smile" style="background:none;" onclick="add_smile(':sm340:')">
                        <img src="/assets/img/smiles/smile (341).png" id="smile" style="background:none;" onclick="add_smile(':sm341:')">
                        <img src="/assets/img/smiles/smile (342).png" id="smile" style="background:none;" onclick="add_smile(':sm342:')">
                        <img src="/assets/img/smiles/smile (343).png" id="smile" style="background:none;" onclick="add_smile(':sm343:')">
                        <img src="/assets/img/smiles/smile (344).png" id="smile" style="background:none;" onclick="add_smile(':sm344:')">
                        <img src="/assets/img/smiles/smile (345).png" id="smile" style="background:none;" onclick="add_smile(':sm345:')">
                        <img src="/assets/img/smiles/smile (346).png" id="smile" style="background:none;" onclick="add_smile(':sm346:')">
                        <img src="/assets/img/smiles/smile (347).png" id="smile" style="background:none;" onclick="add_smile(':sm347:')">
                        <img src="/assets/img/smiles/smile (348).png" id="smile" style="background:none;" onclick="add_smile(':sm348:')">
                        <img src="/assets/img/smiles/smile (349).png" id="smile" style="background:none;" onclick="add_smile(':sm349:')">
                        <img src="/assets/img/smiles/smile (350).png" id="smile" style="background:none;" onclick="add_smile(':sm350:')">
                        <img src="/assets/img/smiles/smile (351).png" id="smile" style="background:none;" onclick="add_smile(':sm351:')">
                        <img src="/assets/img/smiles/smile (352).png" id="smile" style="background:none;" onclick="add_smile(':sm352:')">
                        <img src="/assets/img/smiles/smile (353).png" id="smile" style="background:none;" onclick="add_smile(':sm353:')">
                        <img src="/assets/img/smiles/smile (354).png" id="smile" style="background:none;" onclick="add_smile(':sm354:')">
                        <img src="/assets/img/smiles/smile (355).png" id="smile" style="background:none;" onclick="add_smile(':sm355:')">
                        <img src="/assets/img/smiles/smile (356).png" id="smile" style="background:none;" onclick="add_smile(':sm356:')">
                        <img src="/assets/img/smiles/smile (357).png" id="smile" style="background:none;" onclick="add_smile(':sm357:')">
                        <img src="/assets/img/smiles/smile (358).png" id="smile" style="background:none;" onclick="add_smile(':sm358:')">
                        <img src="/assets/img/smiles/smile (359).png" id="smile" style="background:none;" onclick="add_smile(':sm359:')">
                        <img src="/assets/img/smiles/smile (360).png" id="smile" style="background:none;" onclick="add_smile(':sm360:')">
                        <img src="/assets/img/smiles/smile (361).png" id="smile" style="background:none;" onclick="add_smile(':sm361:')">
                        <img src="/assets/img/smiles/smile (362).png" id="smile" style="background:none;" onclick="add_smile(':sm362:')">
                        <img src="/assets/img/smiles/smile (363).png" id="smile" style="background:none;" onclick="add_smile(':sm363:')">
                        <img src="/assets/img/smiles/smile (364).png" id="smile" style="background:none;" onclick="add_smile(':sm364:')">
                        <img src="/assets/img/smiles/smile (365).png" id="smile" style="background:none;" onclick="add_smile(':sm365:')">
                        <img src="/assets/img/smiles/smile (366).png" id="smile" style="background:none;" onclick="add_smile(':sm366:')">
                        <img src="/assets/img/smiles/smile (367).png" id="smile" style="background:none;" onclick="add_smile(':sm367:')">
                        <img src="/assets/img/smiles/smile (368).png" id="smile" style="background:none;" onclick="add_smile(':sm368:')">
                        <img src="/assets/img/smiles/smile (369).png" id="smile" style="background:none;" onclick="add_smile(':sm369:')">
                        <img src="/assets/img/smiles/smile (370).png" id="smile" style="background:none;" onclick="add_smile(':sm370:')">
                        <img src="/assets/img/smiles/smile (371).png" id="smile" style="background:none;" onclick="add_smile(':sm371:')">
                        <img src="/assets/img/smiles/smile (372).png" id="smile" style="background:none;" onclick="add_smile(':sm372:')">
                        <img src="/assets/img/smiles/smile (373).png" id="smile" style="background:none;" onclick="add_smile(':sm373:')">
                        <img src="/assets/img/smiles/smile (374).png" id="smile" style="background:none;" onclick="add_smile(':sm374:')">
                        <img src="/assets/img/smiles/smile (375).png" id="smile" style="background:none;" onclick="add_smile(':sm375:')">
                        <img src="/assets/img/smiles/smile (376).png" id="smile" style="background:none;" onclick="add_smile(':sm376:')">
                        <img src="/assets/img/smiles/smile (377).png" id="smile" style="background:none;" onclick="add_smile(':sm377:')">
                        <img src="/assets/img/smiles/smile (378).png" id="smile" style="background:none;" onclick="add_smile(':sm378:')">
                        <img src="/assets/img/smiles/smile (379).png" id="smile" style="background:none;" onclick="add_smile(':sm379:')">
                        <img src="/assets/img/smiles/smile (380).png" id="smile" style="background:none;" onclick="add_smile(':sm380:')">
                        <img src="/assets/img/smiles/smile (381).png" id="smile" style="background:none;" onclick="add_smile(':sm381:')">
                        <img src="/assets/img/smiles/smile (382).png" id="smile" style="background:none;" onclick="add_smile(':sm382:')">
                        <img src="/assets/img/smiles/smile (383).png" id="smile" style="background:none;" onclick="add_smile(':sm383:')">
                        <img src="/assets/img/smiles/smile (384).png" id="smile" style="background:none;" onclick="add_smile(':sm384:')">
                        <img src="/assets/img/smiles/smile (385).png" id="smile" style="background:none;" onclick="add_smile(':sm385:')">
                        <img src="/assets/img/smiles/smile (386).png" id="smile" style="background:none;" onclick="add_smile(':sm386:')">
                        <img src="/assets/img/smiles/smile (387).png" id="smile" style="background:none;" onclick="add_smile(':sm387:')">
                        <img src="/assets/img/smiles/smile (388).png" id="smile" style="background:none;" onclick="add_smile(':sm388:')">
                        <img src="/assets/img/smiles/smile (389).png" id="smile" style="background:none;" onclick="add_smile(':sm389:')">
                        <img src="/assets/img/smiles/smile (390).png" id="smile" style="background:none;" onclick="add_smile(':sm390:')">
                        <img src="/assets/img/smiles/smile (391).png" id="smile" style="background:none;" onclick="add_smile(':sm391:')">
                        <img src="/assets/img/smiles/smile (392).png" id="smile" style="background:none;" onclick="add_smile(':sm392:')">
                        <img src="/assets/img/smiles/smile (393).png" id="smile" style="background:none;" onclick="add_smile(':sm393:')">
                        <img src="/assets/img/smiles/smile (394).png" id="smile" style="background:none;" onclick="add_smile(':sm394:')">
                        <img src="/assets/img/smiles/smile (395).png" id="smile" style="background:none;" onclick="add_smile(':sm395:')">
                        <img src="/assets/img/smiles/smile (396).png" id="smile" style="background:none;" onclick="add_smile(':sm396:')">
                        <img src="/assets/img/smiles/smile (397).png" id="smile" style="background:none;" onclick="add_smile(':sm397:')">
                        <img src="/assets/img/smiles/smile (398).png" id="smile" style="background:none;" onclick="add_smile(':sm398:')">
                        <img src="/assets/img/smiles/smile (399).png" id="smile" style="background:none;" onclick="add_smile(':sm399:')">
                        <img src="/assets/img/smiles/smile (400).png" id="smile" style="background:none;" onclick="add_smile(':sm400:')">
                        <img src="/assets/img/smiles/smile (401).png" id="smile" style="background:none;" onclick="add_smile(':sm401:')">
                        <img src="/assets/img/smiles/smile (402).png" id="smile" style="background:none;" onclick="add_smile(':sm402:')">
                        <img src="/assets/img/smiles/smile (403).png" id="smile" style="background:none;" onclick="add_smile(':sm403:')">
                        <img src="/assets/img/smiles/smile (404).png" id="smile" style="background:none;" onclick="add_smile(':sm404:')">
                        <img src="/assets/img/smiles/smile (405).png" id="smile" style="background:none;" onclick="add_smile(':sm405:')">
                        <img src="/assets/img/smiles/smile (406).png" id="smile" style="background:none;" onclick="add_smile(':sm406:')">
                        <img src="/assets/img/smiles/smile (407).png" id="smile" style="background:none;" onclick="add_smile(':sm407:')">
                        <img src="/assets/img/smiles/smile (408).png" id="smile" style="background:none;" onclick="add_smile(':sm408:')">
                        <img src="/assets/img/smiles/smile (409).png" id="smile" style="background:none;" onclick="add_smile(':sm409:')">
                        <img src="/assets/img/smiles/smile (410).png" id="smile" style="background:none;" onclick="add_smile(':sm410:')">
                        <img src="/assets/img/smiles/smile (411).png" id="smile" style="background:none;" onclick="add_smile(':sm411:')">
                        <img src="/assets/img/smiles/smile (412).png" id="smile" style="background:none;" onclick="add_smile(':sm412:')">
                        <img src="/assets/img/smiles/smile (413).png" id="smile" style="background:none;" onclick="add_smile(':sm413:')">
                        <img src="/assets/img/smiles/smile (414).png" id="smile" style="background:none;" onclick="add_smile(':sm414:')">
                        <img src="/assets/img/smiles/smile (415).png" id="smile" style="background:none;" onclick="add_smile(':sm415:')">
                        <img src="/assets/img/smiles/smile (416).png" id="smile" style="background:none;" onclick="add_smile(':sm416:')">
                        <img src="/assets/img/smiles/smile (417).png" id="smile" style="background:none;" onclick="add_smile(':sm417:')">
                        <img src="/assets/img/smiles/smile (418).png" id="smile" style="background:none;" onclick="add_smile(':sm418:')">
                        <img src="/assets/img/smiles/smile (419).png" id="smile" style="background:none;" onclick="add_smile(':sm419:')">
                        <img src="/assets/img/smiles/smile (420).png" id="smile" style="background:none;" onclick="add_smile(':sm420:')">
                        <img src="/assets/img/smiles/smile (421).png" id="smile" style="background:none;" onclick="add_smile(':sm421:')">
                        <img src="/assets/img/smiles/smile (422).png" id="smile" style="background:none;" onclick="add_smile(':sm422:')">
                        <img src="/assets/img/smiles/smile (423).png" id="smile" style="background:none;" onclick="add_smile(':sm423:')">
                        <img src="/assets/img/smiles/smile (424).png" id="smile" style="background:none;" onclick="add_smile(':sm424:')">
                        <img src="/assets/img/smiles/smile (425).png" id="smile" style="background:none;" onclick="add_smile(':sm425:')">
                        <img src="/assets/img/smiles/smile (426).png" id="smile" style="background:none;" onclick="add_smile(':sm426:')">
                        <img src="/assets/img/smiles/smile (427).png" id="smile" style="background:none;" onclick="add_smile(':sm427:')">
                        <img src="/assets/img/smiles/smile (428).png" id="smile" style="background:none;" onclick="add_smile(':sm428:')">
                        <img src="/assets/img/smiles/smile (429).png" id="smile" style="background:none;" onclick="add_smile(':sm429:')">
                        <img src="/assets/img/smiles/smile (430).png" id="smile" style="background:none;" onclick="add_smile(':sm430:')">
                        <img src="/assets/img/smiles/smile (431).png" id="smile" style="background:none;" onclick="add_smile(':sm431:')">
                        <img src="/assets/img/smiles/smile (432).png" id="smile" style="background:none;" onclick="add_smile(':sm432:')">
                        <img src="/assets/img/smiles/smile (433).png" id="smile" style="background:none;" onclick="add_smile(':sm433:')">
                        <img src="/assets/img/smiles/smile (434).png" id="smile" style="background:none;" onclick="add_smile(':sm434:')">
                        <img src="/assets/img/smiles/smile (435).png" id="smile" style="background:none;" onclick="add_smile(':sm435:')">
                        <img src="/assets/img/smiles/smile (436).png" id="smile" style="background:none;" onclick="add_smile(':sm436:')">
                        <img src="/assets/img/smiles/smile (437).png" id="smile" style="background:none;" onclick="add_smile(':sm437:')">
                        <img src="/assets/img/smiles/smile (438).png" id="smile" style="background:none;" onclick="add_smile(':sm438:')">
                        <img src="/assets/img/smiles/smile (439).png" id="smile" style="background:none;" onclick="add_smile(':sm439:')">
                        <img src="/assets/img/smiles/smile (440).png" id="smile" style="background:none;" onclick="add_smile(':sm440:')">
                        <img src="/assets/img/smiles/smile (441).png" id="smile" style="background:none;" onclick="add_smile(':sm441:')">
                        <img src="/assets/img/smiles/smile (442).png" id="smile" style="background:none;" onclick="add_smile(':sm442:')">
                        <img src="/assets/img/smiles/smile (443).png" id="smile" style="background:none;" onclick="add_smile(':sm443:')">
                        <img src="/assets/img/smiles/smile (444).png" id="smile" style="background:none;" onclick="add_smile(':sm444:')">
                        <img src="/assets/img/smiles/smile (445).png" id="smile" style="background:none;" onclick="add_smile(':sm445:')">
                        <img src="/assets/img/smiles/smile (446).png" id="smile" style="background:none;" onclick="add_smile(':sm446:')">
                        <img src="/assets/img/smiles/smile (447).png" id="smile" style="background:none;" onclick="add_smile(':sm447:')">
                        <img src="/assets/img/smiles/smile (448).png" id="smile" style="background:none;" onclick="add_smile(':sm448:')">
                        <img src="/assets/img/smiles/smile (449).png" id="smile" style="background:none;" onclick="add_smile(':sm449:')">
                        <img src="/assets/img/smiles/smile (450).png" id="smile" style="background:none;" onclick="add_smile(':sm450:')">
                        <img src="/assets/img/smiles/smile (451).png" id="smile" style="background:none;" onclick="add_smile(':sm451:')">
                        <img src="/assets/img/smiles/smile (452).png" id="smile" style="background:none;" onclick="add_smile(':sm452:')">
                        <img src="/assets/img/smiles/smile (453).png" id="smile" style="background:none;" onclick="add_smile(':sm453:')">
                        <img src="/assets/img/smiles/smile (454).png" id="smile" style="background:none;" onclick="add_smile(':sm454:')">
                        <img src="/assets/img/smiles/smile (455).png" id="smile" style="background:none;" onclick="add_smile(':sm455:')">
                        <img src="/assets/img/smiles/smile (456).png" id="smile" style="background:none;" onclick="add_smile(':sm456:')">
                        <img src="/assets/img/smiles/smile (457).png" id="smile" style="background:none;" onclick="add_smile(':sm457:')">
                        <img src="/assets/img/smiles/smile (458).png" id="smile" style="background:none;" onclick="add_smile(':sm458:')">
                        <img src="/assets/img/smiles/smile (459).png" id="smile" style="background:none;" onclick="add_smile(':sm459:')">
                        <img src="/assets/img/smiles/smile (460).png" id="smile" style="background:none;" onclick="add_smile(':sm460:')">
                        <img src="/assets/img/smiles/smile (461).png" id="smile" style="background:none;" onclick="add_smile(':sm461:')">
                        <img src="/assets/img/smiles/smile (462).png" id="smile" style="background:none;" onclick="add_smile(':sm462:')">
                        <img src="/assets/img/smiles/smile (463).png" id="smile" style="background:none;" onclick="add_smile(':sm463:')">
                        <img src="/assets/img/smiles/smile (464).png" id="smile" style="background:none;" onclick="add_smile(':sm464:')">
                        <img src="/assets/img/smiles/smile (465).png" id="smile" style="background:none;" onclick="add_smile(':sm465:')">
                        <img src="/assets/img/smiles/smile (466).png" id="smile" style="background:none;" onclick="add_smile(':sm466:')">
                        <img src="/assets/img/smiles/smile (467).png" id="smile" style="background:none;" onclick="add_smile(':sm467:')">
                        <img src="/assets/img/smiles/smile (468).png" id="smile" style="background:none;" onclick="add_smile(':sm468:')">
                        <img src="/assets/img/smiles/smile (469).png" id="smile" style="background:none;" onclick="add_smile(':sm469:')">
                        <img src="/assets/img/smiles/smile (470).png" id="smile" style="background:none;" onclick="add_smile(':sm470:')">
                        <img src="/assets/img/smiles/smile (471).png" id="smile" style="background:none;" onclick="add_smile(':sm471:')">
                        <img src="/assets/img/smiles/smile (472).png" id="smile" style="background:none;" onclick="add_smile(':sm472:')">
                        <img src="/assets/img/smiles/smile (473).png" id="smile" style="background:none;" onclick="add_smile(':sm473:')">
                        <img src="/assets/img/smiles/smile (474).png" id="smile" style="background:none;" onclick="add_smile(':sm474:')">
                        <img src="/assets/img/smiles/smile (475).png" id="smile" style="background:none;" onclick="add_smile(':sm475:')">
                        <img src="/assets/img/smiles/smile (476).png" id="smile" style="background:none;" onclick="add_smile(':sm476:')">
                        <img src="/assets/img/smiles/smile (477).png" id="smile" style="background:none;" onclick="add_smile(':sm477:')">
                        <img src="/assets/img/smiles/smile (478).png" id="smile" style="background:none;" onclick="add_smile(':sm478:')">
                        <img src="/assets/img/smiles/smile (479).png" id="smile" style="background:none;" onclick="add_smile(':sm479:')">
                        <img src="/assets/img/smiles/smile (480).png" id="smile" style="background:none;" onclick="add_smile(':sm480:')">
                        <img src="/assets/img/smiles/smile (481).png" id="smile" style="background:none;" onclick="add_smile(':sm481:')">
                        <img src="/assets/img/smiles/smile (482).png" id="smile" style="background:none;" onclick="add_smile(':sm482:')">
                        <img src="/assets/img/smiles/smile (483).png" id="smile" style="background:none;" onclick="add_smile(':sm483:')">
                        <img src="/assets/img/smiles/smile (484).png" id="smile" style="background:none;" onclick="add_smile(':sm484:')">
                        <img src="/assets/img/smiles/smile (485).png" id="smile" style="background:none;" onclick="add_smile(':sm485:')">
                        <img src="/assets/img/smiles/smile (486).png" id="smile" style="background:none;" onclick="add_smile(':sm486:')">
                        <img src="/assets/img/smiles/smile (487).png" id="smile" style="background:none;" onclick="add_smile(':sm487:')">
                        <img src="/assets/img/smiles/smile (488).png" id="smile" style="background:none;" onclick="add_smile(':sm488:')">
                        <img src="/assets/img/smiles/smile (489).png" id="smile" style="background:none;" onclick="add_smile(':sm489:')">
                        <img src="/assets/img/smiles/smile (490).png" id="smile" style="background:none;" onclick="add_smile(':sm490:')">
                        <img src="/assets/img/smiles/smile (491).png" id="smile" style="background:none;" onclick="add_smile(':sm491:')">
                        <img src="/assets/img/smiles/smile (492).png" id="smile" style="background:none;" onclick="add_smile(':sm492:')">
                        <img src="/assets/img/smiles/smile (493).png" id="smile" style="background:none;" onclick="add_smile(':sm493:')">
                        <img src="/assets/img/smiles/smile (494).png" id="smile" style="background:none;" onclick="add_smile(':sm494:')">
                        <img src="/assets/img/smiles/smile (495).png" id="smile" style="background:none;" onclick="add_smile(':sm495:')">
                        <img src="/assets/img/smiles/smile (496).png" id="smile" style="background:none;" onclick="add_smile(':sm496:')">
                        <img src="/assets/img/smiles/smile (497).png" id="smile" style="background:none;" onclick="add_smile(':sm497:')">
                        <img src="/assets/img/smiles/smile (498).png" id="smile" style="background:none;" onclick="add_smile(':sm498:')">
                        <img src="/assets/img/smiles/smile (499).png" id="smile" style="background:none;" onclick="add_smile(':sm499:')">
                        <img src="/assets/img/smiles/smile (500).png" id="smile" style="background:none;" onclick="add_smile(':sm500:')">
                        <img src="/assets/img/smiles/smile (501).png" id="smile" style="background:none;" onclick="add_smile(':sm501:')">
                        <img src="/assets/img/smiles/smile (502).png" id="smile" style="background:none;" onclick="add_smile(':sm502:')">
                        <img src="/assets/img/smiles/smile (503).png" id="smile" style="background:none;" onclick="add_smile(':sm503:')">
                        <img src="/assets/img/smiles/smile (504).png" id="smile" style="background:none;" onclick="add_smile(':sm504:')">
                        <img src="/assets/img/smiles/smile (505).png" id="smile" style="background:none;" onclick="add_smile(':sm505:')">
                        <img src="/assets/img/smiles/smile (506).png" id="smile" style="background:none;" onclick="add_smile(':sm506:')">
                        <img src="/assets/img/smiles/smile (507).png" id="smile" style="background:none;" onclick="add_smile(':sm507:')">
                        <img src="/assets/img/smiles/smile (508).png" id="smile" style="background:none;" onclick="add_smile(':sm508:')">
                        <img src="/assets/img/smiles/smile (509).png" id="smile" style="background:none;" onclick="add_smile(':sm509:')">
                        <img src="/assets/img/smiles/smile (510).png" id="smile" style="background:none;" onclick="add_smile(':sm510:')">
                        <img src="/assets/img/smiles/smile (511).png" id="smile" style="background:none;" onclick="add_smile(':sm511:')">
                        <img src="/assets/img/smiles/smile (512).png" id="smile" style="background:none;" onclick="add_smile(':sm512:')">
                        <img src="/assets/img/smiles/smile (513).png" id="smile" style="background:none;" onclick="add_smile(':sm513:')">
                        <img src="/assets/img/smiles/smile (514).png" id="smile" style="background:none;" onclick="add_smile(':sm514:')">
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