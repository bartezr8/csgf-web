@extends('layout')

@section('content')
<title>  {{ $title = 'КОСТИ | ' }}</title>
<link href="{{ $asset('assets/css/dice.css') }}" rel="stylesheet">
<div class="content">
    <div class="title-block">
        <h2 style="color: #ffffff;">Кости</h2>
    </div>
    <div class="page-content"style="">
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> ПОСЛЕДНИЕ ПОБЕДИТЕЛИ</b></div>
        <br>
        <div id="DiceCarouselConatiner" class="player-list" style="width: 1010px;z-index: 0;overflow: hidden;bottom: 14px;left: 0;height: 70px;">
            <ul id="DiceCarousel" class="list-reset" style="">
                @foreach($games as $i)
                    <li style="height: 70px;display: block;float: left;margin-right: 7px;">
                        <img style="opacity: 0.8;width: 70px;height: 70px;border-radius: 3px;" id="" src="{{ $i['avatar'] }}">
                        <div class="chance" id="div_winner_112">{{ $i['win'] }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
        <br>
        <div class="info_title"><b style="float: left; margin-left: 10px;"><i class="info_icon"></i> ВАШ СТОЛ С КОСТЯМИ</b></div>
        <div class="dice">
            <div id="roll-wrap" style="padding-top: 35px;height: 145px;perspective: 800;perspective-origin: 50% 200px;width: 145px;float: left;display: inline-block;">
                <div id="dice" style="transform: rotateX(40deg) rotateY(40deg);position: relative;margin: 10px auto 0;height: 100px;width: 100px;transition: transform 500ms linear;transform-style: preserve-3d;" data-rotatex="40" data-rotatey="40">
                    <div class="sf f1 inner"></div>
                    <div class="sf f2 inner"></div>
                    <div class="sf f3 inner"></div>
                    <div class="sf f4 inner"></div>
                    <div class="sf f5 inner"></div>
                    <div class="sf f6 inner"></div>
                    <div class="sf f1">
                        <div class="dot center"></div>
                    </div>
                    <div class="sf f2">
                        <div class="dot dtop dleft"></div>
                        <div class="dot dbottom dright"></div>
                    </div>
                    <div class="sf f3">
                        <div class="dot dtop dleft"></div>
                        <div class="dot center"></div>
                        <div class="dot dbottom dright"></div>
                    </div>
                    <div class="sf f4">
                        <div class="dot dtop dleft"></div>
                        <div class="dot dtop dright"></div>
                        <div class="dot dbottom dleft"></div>
                        <div class="dot dbottom dright"></div>
                    </div>
                    <div class="sf f5">
                        <div class="dot center"></div>
                        <div class="dot dtop dleft"></div>
                        <div class="dot dtop dright"></div>
                        <div class="dot dbottom dleft"></div>
                        <div class="dot dbottom dright"></div>
                    </div>
                    <div class="sf f6">
                        <div class="dot dtop dleft"></div>
                        <div class="dot dtop dright"></div>
                        <div class="dot dbottom dleft"></div>
                        <div class="dot dbottom dright"></div>
                        <div class="dot center dleft"></div>
                        <div class="dot center dright"></div>
                    </div>

                    <div class="sf cover x"></div>
                    <div class="sf cover y"></div>
                    <div class="sf cover z"></div>
                </div>
            </div>
            <div class="controls" style="position: relative;left: 20px;top: 35px;">
                <input type="text" class="amount" value="" style="float: left; margin-left:4px" placeholder="Ставка" id="betAmount">
                <div class="buttons">
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="clear">Очистить</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="min">Мин</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="+1">+1</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="+10">+10</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="+100">+100</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="1/2">1/2</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="x2">x2</div>
                    <div class="balance-button" style="padding: 5px 23px;float: left; margin-left:4px" data-action="max">Макс</div>
                </div>
            </div>
            <div class="play-area" style="width: 860px;display: flex;position: relative;left: 17px;top: 28px;">
                <div class="dice-colors">
                    <div class="dice-color red">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button red" data-value="1">1</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color green">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button green" data-value="2">2</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color black">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button black" data-value="3">3</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color orange">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button orange" data-value="4">4</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color blue">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button blue" data-value="5">5</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color red">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button red" data-value="6">6</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x6</span></div>
                        </div>
                        
                    </div>
                </div>
                <div class="dice-colors">
                    <div class="dice-color green">
                        <div class="color-header">
                            <div class="header-info">
                                <button class="header-button green" data-value="low" style="padding: 15px;">1 to 3</button>
                            </div>
                        </div>
                        <div class="dice-your-bet">
                            <div><span class="my-bet-red my-bet">x2</span></div>
                        </div>
                        
                    </div>
                    <div class="dice-color red">
                    <div class="color-header">
                        <div class="header-info">
                            <button class="header-button red" data-value="high" style="padding: 15px;">4 to 6</button>
                        </div>
                    </div>
                    <div class="dice-your-bet">
                        <div><span class="my-bet-red my-bet">x2</span></div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection