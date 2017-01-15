function n2w(n, w) {
    n %= 100;
    if(n > 19) n %= 10;
    switch(n) {
        case 1:
            return w[0];
        case 2:
        case 3:
        case 4:
            return w[1];
        default:
            return w[2];
    }
}
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
function updateData(message){
    var data = message.data;
    $('#lw').addClass("flip");
    $('#mltd').addClass("flip");
    $('#mlf').addClass("flip");
    setTimeout(function() {
        $('#lw-name').html('<a href="/user/' + data.lw.user.steamid64 + '" class="color-yellow">' + data.lw.user.username + '</a>');
        $('#lw-avatar').html('<a href="/user/' + data.lw.user.steamid64 + '"><img src="' + data.lw.user.avatar + '" alt="" title="" /></a>');
        $('#lw-chance').html('Шанс: <span class="down-text">' + data.lw.chance + '%</span>');
        $('#lw-money').html('Сумма выигрыша: <span class="down-text">' + data.lw.price + ' Р</span>');
        $('#mltd-name').html('<a href="/user/' + data.mltd.user.steamid64 + '" class="color-yellow">' + data.mltd.user.username + '</a>');
        $('#mltd-avatar').html('<a href="/user/' + data.mltd.user.steamid64 + '"><img src="' + data.mltd.user.avatar + '" alt="" title="" /></a>');
        $('#mltd-chance').html('Шанс: <span class="down-text">' + data.mltd.chance + '%</span>');
        $('#mltd-money').html('Сумма выигрыша: <span class="down-text">' + data.mltd.price + ' Р</span>');
        $('#mlf-name').html('<a href="/user/' + data.mlfv.user.steamid64 + '" class="color-yellow">' + data.mlfv.user.username + '</a>');
        $('#mlf-avatar').html('<a href="/user/' + data.mlfv.user.steamid64 + '"><img src="' + data.mlfv.user.avatar + '" alt="" title="" /></a>');
        $('#mlf-chance').html('Шанс: <span class="down-text">' + data.mlfv.chance + '%</span>');
        $('#mlf-money').html('Сумма выигрыша: <span class="down-text">' + data.mlfv.price + ' Р</span>');
        setTimeout(function() {
            $('#lw').removeClass("flip");
            $('#mltd').removeClass("flip");
            $('#mlf').removeClass("flip");
        }, 300);
    }, 925);
    setTimeout(function() {
        var last = Math.abs(data.last);
        $(".stats-last-href").attr("href", "/game/" + last);
        $(".stats-last").addClass("num_anim");
        $(".stats-total").addClass("num_anim");
        $(".stats-max").addClass("num_anim");
        $(".stats-uToday").addClass("num_anim");
        setTimeout(function() {
            $(".stats-last").text(last);
            $(".stats-total").text(data.total);
            $(".stats-max").text(data.max);
            $(".stats-uToday").text(data.today);
            setTimeout(function() {
                $(".stats-last").removeClass("num_anim");
                $(".stats-total").removeClass("num_anim");
                $(".stats-max").removeClass("num_anim");
                $(".stats-uToday").removeClass("num_anim");
            }, 250);
        }, 750);
    }, 300);
}
function fillWinnerInfo(data) {
    data = data || {
        winner: {}
    };
    var obj = {
        totalPrice: data.game.price || 0,
        number: data.game.price ? ('#' + Math.floor(data.round_number * data.game.price)) : '???',
        tickets: data.tickets || 0,
        winner: {
            image: data.winner.avatar || '???',
            login: data.winner.username || '???',
            id: data.winner.steamid64 || '#',
            chance: data.chance || 0,
            winTicket: data.ticket || '???'
        }
    };
    $('#winnerInfo .winner-info-holder').hide();
    $('#winnerInfo #winTicket').text('#' + obj.winner.winTicket);
    $('#winnerInfo #totalTickets').text(obj.tickets);
    $('#winnerInfo img').attr('src', obj.winner.image);
    $('#winnerInfo #winnerLink').text(obj.winner.login);
    $('#winnerInfo #winnerLink').attr('href', '/user/' + obj.winner.id);
    $('#winnerInfo #winnerChance').text('(ШАНС: ' + obj.winner.chance.toFixed(2) + '%)');
    $('#winnerInfo #winnerSum').text(obj.totalPrice);
    updateChatMargin();
}
function sortByChance(arrayPtr) {
    var temp = [],
        item = 0;
    for(var counter = 0; counter < arrayPtr.length; counter++) {
        temp = arrayPtr[counter];
        item = counter - 1;
        while(item >= 0 && arrayPtr[item].chance < temp.chance) {
            arrayPtr[item + 1] = arrayPtr[item];
            arrayPtr[item] = temp;
            item--;
        }
    }
    return arrayPtr;
}
function checkUrl(url) {
    var pathname = window.location.pathname;
    if(pathname == url) { return true; } else { return false; }
}
function playBetSound(price) {
    if(price < 10) {
        var audio = new Audio('/assets/sounds/betLow.mp3');
    } else if(price < 50) {
        var audio = new Audio('/assets/sounds/betMedium.mp3');
    } else {
        var audio = new Audio('/assets/sounds/betHigh.mp3');
    }
    if(sound_status) audio.play();
}
function updateScrollbar() {
    $('.current-chance-block').perfectScrollbar('destroy');
    $('.current-chance-block').perfectScrollbar({
        suppressScrollY: true,
        useBothWheelAxes: true
    });
}
function num(val) {
    return Math.round(parseFloat(val) * 100) / 100;
}
function updateUsers(){
    var r = Math.floor(Math.sqrt(57660 / $('#win-block').children().length)) - 1;
    $(".onine_user").css({
        'height': r,
        'width': r
    });
    $('.onine_user').tooltip({
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
}
function toggleChat() {
    var mainContainer = $('.main-container'),
        dadContainer = $('.dad-container'),
        chatBody = $('#chatBody'),
        chatHeader = $('#chatHeader'),
        chatClose = $('#chatClose'),
        chatContainer = $('#chatContainer'),
        chatScroll = $('#chatScroll'),
        viewPortHeight = $(window).innerHeight(),
        viewPortWidth = $(window).innerWidth();
    chatContainer.css({"height": viewPortHeight});
    $(window).resize(function () {
        viewPortHeight = $(window).innerHeight();
        chatContainer.css({"height": viewPortHeight});
    });
    $('body').append(chatHeader);
    if (getCookie('chat') !== '0') {
        mainContainer.addClass('with-chat').find('.dad-container').addClass('with-chat');
        chatContainer.show();
    } else {
        chatHeader.fadeIn();
    }
    var timerChatCheck = setInterval(updateChatMargin, 1000);
    chatScroll.perfectScrollbar();
    chatClose.on('click', function (e) {
        e.preventDefault();
        document.cookie = "chat=0";
        chatContainer.animate({width: 'toggle'}, 400, function () {
            $('meta[name=viewport]').attr('content', 'width=1050');
            mainContainer.toggleClass('with-chat').find('.dad-container').toggleClass('with-chat');
            chatHeader.fadeIn();
        });
    });
    chatHeader.on('click', function (e) {
        e.preventDefault();
        $(this).fadeOut();
        document.cookie = "chat=1";
        mainContainer.removeClass('big-padding');
        $('meta[name=viewport]').attr('content', 'width=1280');
        mainContainer.toggleClass('with-chat').find('.dad-container').toggleClass('with-chat');
        chatContainer.animate({width: 'toggle'}, 400);
    });
    $(window).bind('scroll.chatScroll', function () {
        var dadHeight = dadContainer.innerHeight(),
            chatContHeight = chatContainer.innerHeight(),
            scrollTop = $(this).scrollTop();

        if (dadHeight > chatContHeight) {
            chatContainer.css({
                "margin-top": scrollTop
            });
        }
    });
    if (viewPortWidth < 1360) {
        chatClose.trigger('click');
    }
}
function updateChatMargin() {
    var chatScroll = $('#chatScroll'),
        windowHeight = $(window).innerHeight(),
        chatInput = $('#chatInput'),
        chatForm = $('.chat-form'),
        chatNotLogged = $('#notLoggedIn'),
        chatPrompt = $('.chat-prompt'),
        dadCont = $('.dad-container'),
        dadContHeight = dadCont.innerHeight(),
        chatContainer = $('#chatContainer'),
        chatHeight = windowHeight;

    if (chatInput.length) {
        chatHeight = chatHeight - chatForm.innerHeight() - 18;
    } else {
        chatHeight = chatHeight - chatNotLogged.innerHeight() - 15;
    }
    if (chatPrompt.length) {
        chatHeight = chatHeight - chatPrompt.innerHeight();
    }
    chatScroll.css({'height': chatHeight});
    if (dadContHeight <= windowHeight) {
        chatContainer.css({"margin-top": 0});
    } else {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        chatContainer.css({
            "margin-top": scrollTop
        });
    }
}
function loadMyInventory() {
    $('thead').hide();
    $.ajax({
        url: '/myinventory',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            var text = '<tr><td colspan="4" style="text-align: center">Произошла ошибка. Попробуйте еще раз</td></tr>';
            var totalPrice = 0;
            if(data.success && data.items) {
                text = '';
                var items = data.items;
                items.sort(function(a, b) {
                    return b.price - a.price
                });
                _.each(items, function(item) {
                    item.price = item.price || 0;
                    totalPrice += parseFloat(item.price);
                    item.price = item.price;
                    item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item.classid + '/200fx200f';
                    item.market_name = item.market_name || '';
                    text += '' +
                        '<tr>' +
                        '<td class="item-image"><div class="item-image-wrap">' + '<img src="' + item.image + '">' + '</div></td>' +
                        '<td class="item-name">' + item.name + '</td>' +
                        '<td class="item-type">' + item.market_name.replace(item.name, '').replace('(', '').replace(')', '') + '</td>' +
                        '<td class="item-cost">' + (item.price || '---') + '</td>' +
                        '</tr>'
                });
                $('#totalPrice').text(totalPrice.toFixed(2));
                $('thead').show();
            }
            $('tbody').html(text);
        },
        error: function() {
            var text = isEn() ? 'An error has occurred. Try again' : 'РџСЂРѕРёР·РѕС€Р»Р° РѕС€РёР±РєР°. РџРѕРїСЂРѕР±СѓР№С‚Рµ РµС‰Рµ СЂР°Р·';
            $('tbody').html('<tr><td colspan="4" style="text-align: center">' + text + '<td></tr>');
        }
    });
}
function mulAndShuffle(arr, k) {
    var res = [],
        len = arr.length,
        total = k * len,
        rand, prev;
    while(total) {
        rand = arr[Math.floor(Math.random() * len)];
        if(len == 1) {
            res.push(prev = rand);
            total--;
        } else if(rand !== prev) {
            res.push(prev = rand);
            total--;
        }
    }
    for(var j, x, i = res.length; i; j = Math.floor(Math.random() * i), x = res[--i], res[i] = res[j], res[j] = x);
    return res;
}
function lpad(str, length) {
    while(str.toString().length < length)
        str = '0' + str;
    return str;
}
function updateBackground() {
    var mainHeight = $('.dad-container').height();
    var windowHeight = $(window).height();
    if(mainHeight > windowHeight) {
        $('.main-container').height('auto');
    } else {
        $('.main-container').height('auto');
    }
}
function sendMoney(userid) {
    sendUpdate();
    SM_ID = userid;
    $('#smid').attr("href", "/user/" + SM_ID);
    $('#smid').text(SM_ID);
    $('#msend').arcticmodal();
}
function sendUpdate(){
    $('#smb').html('<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
    $.post('/send/list', {}, function(data) {
        data = JSON.parse(data);
        if(data.length>0){
            $('#smb').html('<div class="user-winner-table" style="padding-bottom: 0px;margin: 0px;"><table><thead><tr><td style="text-align: center; padding-left: 0px;" class="winner-name-h">От</td><td style="text-align: center; padding-left: 0px;" class="round-sum-h">Для</td><td style="text-align: center; padding-left: 0px;" class="winner-name-h">Сумма</td></tr></thead><tbody id="smlast"></tbody></table></div>');
            $('#smlast').html('');
            data.forEach(function(item) {
                $('#smlast').prepend('<tr><td class="participations" style="width: 300px;text-align: left;color: #fcffdc;font-weight: 300;width: 300px;"><a href="/user/' + item.money_id_from + '" style="color: #b3e5ff;"><span style="display: inline-block;max-width: 190px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;vertical-align: middle;color: #fcffdc;">' + item.money_from + '</span></a></td><td class="winner-name"  style="width: 300px;"><a href="/user/' + item.money_id_to + '" style="color: #b3e5ff;"><span>' + item.money_to + '</span></a></td><td class="participations">' + item.money_amount + '</td></tr>');
            });
        } else {
            $('#smb').html('<center><h1 style="color: #FFF; font-weight: 300;">Переводы отсутствуют!</h1></center');
        }
    });
}
function load_page() {
    if(!LOAD) {
        LOAD = true;
        var $preloader = $('#page-preloader'),
            $spinner = $preloader.find('.spinner');
        $spinner.fadeOut();
        $preloader.delay(350).fadeOut('slow');
    }
}
function getMenuPosition(mouse, direction, scrollDir) {
    var win = $(window)[direction](),
        scroll = $(window)[scrollDir](),
        menu = $("#contextMenu")[direction](),
        position = mouse + scroll;
        if(direction == 'width') position = $(".dad-container.with-chat")['width']() + 100;
       
    if(mouse + menu > win && menu < mouse)
        position -= menu;
    return position;
}
function add_smile(smile) {
    $('#chatInput').val($('#chatInput').val() + smile);
}
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
function delete_message(id) {
    $.ajax({
        url: '/delmsg',
        type: 'POST',
        dataType: 'json',
        data: {
            id: $('.chatMessage').index($('#chat_msg_' + id))
        },
        success: function(data) {
            $.notify(data.message, {
                clickToHide: 'true',
                autoHide: "false",
                className: data.status
            });
        },
        error: function() {
            $.notify("Произошла ошибка. Попробуйте еще раз", {
                className: "error"
            });
        }
    });
    return;
}
function sendMessage(text) {
    $('#chatInput').val('');
    $.post('/add_message', {
        messages: text
    }, function(message) {
        if(message && message.status) {
            $.notify(message.message, message.status);
        }
    });
}
function update_chat() {
    $.ajax({
        type: "GET",
        url: "/chat",
        dataType: "json",
        cache: false,
        success: function(message) {
            addMsg(message);
        }
    });
}
function delMsg(message){
    $('.chatMessage').eq(message.id).remove();
}
function coin_bet( id ) {
    $.post('/coin/bet', {
        id: id
    },
    function(data) {
        if(data.type == 'success'){
            USER_BALANCE = num(num(USER_BALANCE) - num($("#cointable #coin_" + id + " #sum").text()));
            $('.userBalance').text(USER_BALANCE);
        }
        return $.notify(data.text, data.type);
    });
}
function addMsg(message){
    if(message && message.length > 0) {
        //message = message.reverse();
        for(var i in message) {
            var a = $("#chatScroll")[0];
            var isScrollDown = Math.abs((a.offsetHeight + a.scrollTop) - a.scrollHeight) < 5;
            var CANDEL = false;
            if(IS_MODER == 1) CANDEL = true;
            if(IS_ADMIN == 1) CANDEL = true;
            if(message[i].userid == USER_ID) CANDEL = true;
            var html = '<div class="chatMessage clearfix" id="chat_msg_' + message[i].id + '" data-uuid="' + message[i].id + '" data-user="' + message[i].userid + '" data-username="' + message[i].username + '">';
            html += '<a href="/user/' + message[i].userid + '" target="_blank"><img ';
            if(message[i].admin == 1) {
                html += 'style="border: 1px dashed #FF2D00;" ';
            } else if(message[i].moder == 1) {
                html += 'style="border: 1px dashed #E400B0;" ';
            } else if(message[i].vip == 1) {
                html += 'style="border: 1px dashed #F9FF2F;" ';
            }
            html += 'src="https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/' + _.escape(message[i].avatar).replace('_full', '') + '"></a>';
            if(CANDEL) html += '<div class="delete_message" onclick="delete_message( ' + message[i].id + ' )"><img src="/assets/img/delete.png" alt=""></div>';
            html += '<div class="login" href="/user/' + message[i].userid + '" target="_blank">';
            if(message[i].admin == 1) {
                html += '<span style="color: #FF2D00;">[A]</span> ';
            } else if(message[i].moder == 1) {
                html += '<span style="color: #E400B0;">[M]</span> ';
            } else if(message[i].vip == 1) {
                html += '<span style="color: #F9FF2F;">';
            }
            html += message[i].username + '</div>';
            html += '<div class="timeMessage" style="position: absolute; margin-top: -16px; margin-left: 230px; color: gray; font-size: 12px;">' + message[i].time + '</div>';
            html += '<div class="body">' + message[i].messages + '</div>';
                html += '</div>';
            $('#messages').append(html);
            if($('.chatMessage').length > 50) {
                $('.chatMessage').eq(0).remove();
            }
        }
        if(isScrollDown) a.scrollTop = a.scrollHeight;
        $("#chatScroll").perfectScrollbar('update');
    }
}
if(checkUrl('/double')){
    function plus1(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(parseInt(field_bet) + 1);
    }
    function plus10(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(parseInt(field_bet) + 10);
    }
    function plus100(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(parseInt(field_bet) + 100);
    }
    function plus1000(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(parseInt(field_bet) + 1000);
    }
    function dev2(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(Math.floor(parseInt(field_bet)/2));
    }
    function x2(){
        field_bet = 0;
        if($('input#betAmount').val()) field_bet = $('input#betAmount').val()
        $('input#betAmount').val(parseInt(field_bet)*2);
    }
    function max(){
        field_bet = 0;
        $.post('/getBalance', function (data) {
            $('input#betAmount').val(data);
        });
    }
    function clearr(){
        field_bet = 0;
        $('input#betAmount').val(field_bet);
    }
    function red() {
        money = $('input#betAmount').val();
        $.ajax({
            url: '/double/bet',
            type: 'POST',
            dataType: 'json',
            data: {type: 1, amount: money},
            success: function (data) {
                if (data.success) {
                    $.notify(data.msg, {
                        clickToHide: 'true',
                        autoHide: "false",
                        className: "success"
                    });
                    updateBalance();
                }
                else {
                    if (data.msg) $.notify(data.msg, {className: "error"});
                    updateBalance();
                }
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
                updateBalance();
            }
        });
        return false;
    }
    function green() {
        money = $('input#betAmount').val();
        $.ajax({
            url: '/double/bet',
            type: 'POST',
            dataType: 'json',
            data: {type: 2, amount: money},
            success: function (data) {
                if (data.success) {
                    $.notify(data.msg, {
                        clickToHide: 'true',
                        autoHide: "false",
                        className: "success"
                    });
                    updateBalance();
                }
                else {
                    if (data.msg) $.notify(data.msg, {className: "error"});
                    updateBalance();
                }
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
                updateBalance();
            }
        });
        return false;
    }
    function black() {
        money = $('input#betAmount').val();
        $.ajax({
            url: '/double/bet',
            type: 'POST',
            dataType: 'json',
            data: {type: 3, amount: money},
            success: function (data) {
                if (data.success) {
                    $.notify(data.msg, {
                        clickToHide: 'true',
                        autoHide: "false",
                        className: "success"
                    });
                    updateBalance();
                }
                else {
                    if (data.msg) $.notify(data.msg, {className: "error"});
                    updateBalance();
                }
            },
            error: function () {
                $.notify("Произошла ошибка. Попробуйте еще раз", {
                    className: "error"
                });
                updateBalance();
            }
        });
        return false;
    }
    $(document).ready(function () {
        setTimeout(function () {
            updateBalance()
        }, 1000);
    });
}
var lastMsg = '';
var lastMsgTime = '';
$(function() {
    CSGF.init();
    if(checkUrl('/coin')) window.coin_tpl = _.template($('#coin-template').html());
    if(checkUrl('/dice')){
        window.dice = ({
            _dice: $('#dice'),
            _lastGames: $('#DiceCarousel'),
            moving: false,
            addLastGame: function(data) {
                if (this._lastGames.children().length >= 13) this._lastGames.children().last().remove();
                this._lastGames.prepend('<li class="fade-in-right" style="height: 70px;display: block;float: left;margin-right: 7px;"><img style="opacity: 0.8;width: 70px;height: 70px;border-radius: 3px;" id="" src="' + data.avatar + '"><div class="chance" id="div_winner_112">' + num(data.win) + '</div></li>')
            },
            diceRoll: function(i, again) {
                var rotateXnow = this._dice.data('rotatex');
                var rotateYnow = this._dice.data('rotatey');
                again = again || 3;

                if (again > 1) {
                    rotateXnow = rotateXnow + 270;
                    rotateYnow = rotateYnow + 270;
                    this._dice
                        .css("transform", "rotateX(" + rotateXnow + "deg) rotateY(" + rotateYnow + "deg)")
                        .data('rotatex', rotateXnow)
                        .data('rotatey', rotateYnow);
                    setTimeout(this.diceRoll.bind(this), 500, i, again - 1);
                } else {
                    var rotate = {
                        1: {x: 270, y: 0, n: 6},
                        2: {x: 0, y: 0, n: 4},
                        3: {x: 0, y: 270, n: 5},
                        4: {x: 0, y: 180, n: 2},
                        5: {x: 0, y: 90, n: 3},
                        6: {x: 90, y: 180, n: 1}
                    };

                    var rotateX = Math.ceil(rotateXnow / 360) * 360 + (parseInt(rotate[i].x));
                    var rotateY = Math.ceil(rotateYnow / 360) * 360 + (parseInt(rotate[i].y));
                    this._dice
                        .css("transform", "rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg)")
                        .data('rotatex', rotateX)
                        .data('rotatey', rotateY);
                    setTimeout(this.restart.bind(this), 600);
                }
            },
            roll: function(number) {
                this.moving = true;
                this.diceRoll(number);
            },
            bet: function(value) {
                if (this.moving) return;
                var sum = 0;
                if (!isNaN($('.amount').val())) sum = num($('.amount').val());
                if (sum <= 0) return $.notify('Укажите сумму ставки', 'error');
                $.post('/dice/bet', {
                    sum: sum,
                    value: value
                }, function(data) {
                    if(data.type == 'success') {
                        USER_BALANCE = num(num(USER_BALANCE) - num(sum));
                        $('.userBalance').text(USER_BALANCE);
                        dice.roll(data.value);
                    }
                    return $.notify(data.text, data.type);
                });
            },
            restart: function() {
                this.moving = false;
                updateBalance();
            }
        });
        $('.dice-colors').on('click', 'button', function() {
            dice.bet($(this).data('value'));
        });
        $('.buttons').on('click', '.balance-button', function() {
            var input = $('.amount');
            switch ($(this).data('action')) {
                case 'clear':
                    input.val('');
                    break;
                case 'min':
                    input.val('0.01');
                    break;
                case 'max':
                    $.post('/getBalance', function (data) {input.val(data)});
                    break;
                case '+1':
                    if (isNaN(num(input.val()))) input.val(0);
                    input.val(num(input.val()) + 1);
                    break;
                case '+10':
                    if (isNaN(num(input.val()))) input.val(0);
                    input.val(num(input.val()) + 10);
                    break;
                case '+100':
                    if (isNaN(num(input.val()))) input.val(0);
                    input.val(num(input.val()) + 100);
                    break;
                case '1/2':
                    if (isNaN(input.val())) input.val(0);
                    input.val(num(num(input.val()) / 2));
                    break;
                case 'x2':
                    if (isNaN(input.val())) input.val(0);
                    input.val(num(num(input.val()) * 2));
                    break;
            }
        });
    }
    if(checkUrl('/shop/deposit')){
        window.shop = ({
            shop_loader: '<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>',
            item_tpl: _.template($('#item-template').html()),
            shop_items: {},
            shop_items_Holder: $('#items-list'),
            shop_cart: {},
            shop_cart_price: 0,
            shop_cart_Holder: $('#cart-list'),
            shiftPress: false,
            parce_item: function(zipItem){
                var i = 0;
                zipItem = {
                    id: zipItem[i++],
                    count: zipItem[i++],
                    name: zipItem[i++],
                    priceCent: zipItem[i++],
                    classid: zipItem[i++],
                    exterior: zipItem[i++],
                    rarity: zipItem[i++],
                    rarity_text: zipItem[i++],
                    ids: zipItem[i++],
                    shortexterior: '',
                };
                if (zipItem.exterior == 'Прямо с завода') {
                    zipItem.shortexterior = 'FN';
                } else if (zipItem.exterior == 'Немного поношенное') {
                    zipItem.shortexterior = 'MW';
                } else if (zipItem.exterior == 'После полевых испытаний') {
                    zipItem.shortexterior = 'FT';
                    zipItem.exterior = 'После полевых';
                } else if (zipItem.exterior == 'Поношенное') {
                    zipItem.shortexterior = 'WW';
                } else if (zipItem.exterior == 'Закаленное в боях') {
                    zipItem.shortexterior = 'BS';
                } else if (zipItem.exterior == null) {
                    zipItem.shortexterior = '*';
                    zipItem.exterior = 'Не покрашено';
                } else {
                    zipItem.shortexterior = '*';
                    zipItem.exterior = 'Не покрашено';
                }
                if(zipItem.name.length > 35) {
                    zipItem.name = zipItem.name.substr(0, 34) + '...'
                }
                if(zipItem.exterior !== null) {
                    if (zipItem.exterior.length > 19) {
                        zipItem.exterior = zipItem.exterior.substr(0, 16) + '...'
                    }
                }
                var item_obj = {
                    id: zipItem.id,
                    name: zipItem.name,
                    price: num(zipItem.priceCent),
                    classid: zipItem.classid,
                    shortexterior: zipItem.shortexterior,
                    count: zipItem.count,
                    exterior_all: zipItem.exterior,
                    filter_rarity: zipItem.rarity,
                    rarity_all: zipItem.rarity_text,
                    className: zipItem.rarity,
                    ids: zipItem.ids
                };
                item_obj.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item_obj.classid + '/101fx100f';
                item_obj.el = $(this.item_tpl(item_obj));
                return item_obj;
            },
            draw_items: function(){
                this.shop_items_Holder.children().replaceWith('');
                var items_list = makeArray(this.shop_items);
                var raritys = [], exteriors = []; 
                items_list.forEach(function (item) {
                    if(raritys.indexOf(item.rarity_all) == -1) raritys.push(item.rarity_all);
                    if(exteriors.indexOf(item.exterior_all) == -1) exteriors.push(item.exterior_all);
                });
                $('#rarity_all').children().replaceWith('');
                $('#rarity_all').append('<option value="">Все раритетности</option>');
                raritys.forEach(function(item){
                    $('#rarity_all').append('<option value="' + item + '">' + item + '</option>');
                });
                $('#exterior_all').children().replaceWith('');
                $('#exterior_all').append('<option value="">Любое качество</option>');
                exteriors.forEach(function(item){
                    $('#exterior_all').append('<option value="' + item + '">' + item + '</option>');
                });
                $('#items-total').text(_.reduce(items_list, function (memo, num) {
                    return memo + num.count;
                }, 0));
                $('#filter-total').text(_.reduce(items_list, function (memo, num) {
                    return memo + num.count;
                }, 0));
                if($('#sort_all').val() == 'desc'){
                    items_list.sort(function (a, b) {
                        return b.price - a.price
                    });
                } else {
                    items_list.sort(function (b, a) {
                        return b.price - a.price
                    });
                }
                items_list.forEach(function (item) {
                    item.el = $(shop.item_tpl(item));
                    shop.shop_items_Holder.append(item.el);
                });
                this.show_items();
            },
            draw_cart: function(){
                this.shop_cart_Holder.children().replaceWith('');
                var items_list = makeArray(this.shop_cart);
                items_list.sort(function (a, b) {
                    return b.price - a.price
                });
                var count = 0;
                var price = 0;
                items_list.forEach(function (item) {
                    if(item.count > 0) {
                        item.el = $(shop.item_tpl(item));
                        shop.shop_cart_Holder.append(item.el);
                        count += item.count;
                        price += item.price * item.count;
                    }
                });
                this.shop_cart_price = price;
                $('#cart-total').text(count);
                $('#cart-total-price').text(num(price));
            },
            show_items: function(){
                var args = [];
                var items_list = makeArray(shop.shop_items);
                ['exterior_all', 'rarity_all'].forEach(function(sel) {
                    var sorter = $('#' + sel).val();
                    if (sorter) {
                        var p = _.filter(items_list, function (item) {
                            var _exterior = item[sel];
                            return _exterior == sorter;
                        });
                        p = _.pluck(p, 'id');
                        args.push(p);
                    }
                });
                
                var from = parseFloat($('#priceFrom').val()) || 0;
                var to = parseFloat($('#priceTo').val()) || 10e10;
                if (to < from) to = 10e10;
                var p = _.filter(items_list, function (item) {
                    var _price = num(item.price);
                    return _price >= from && _price <= to;
                });
                p = _.pluck(p, 'id');
                args.push(p);
                var text = $('#searchInput').val().trim();
                text = text.replace('|', '\\|');
                var p = _.filter(items_list, function (item) {
                    return (new RegExp(text, 'i').test(item.name));
                });
                p = _.pluck(p, 'id');
                args.push(p);
                if(args.length){
                    var allItems = shop.shop_items_Holder.children('.deposit-item');
                    var s = _.intersection.apply(null, args);
                    var count = 0;
                    allItems.addClass('hidden');
                    s.forEach(function (id) {
                        if (shop.shop_items[id].count > 0){
                            shop.shop_items[id].el.removeClass('hidden');
                            count += shop.shop_items[id].count;
                        }
                    });
                    $('#filter-total').text(count);
                } else {
                    allItems.removeClass('hidden');
                }
            },
            clear_items: function(){
                this.shop_items = {};
                this.shop_cart = {};
                this.shop_cart_price = 0;
                this.shop_items_Holder.children().replaceWith('');
                this.shop_cart_Holder.children().replaceWith('');
                this.shop_items_Holder.html('');
                $('#items-total').text(0);
                $('#filter-total').text(0);
                $('#cart-total').text(0);
                $('#cart-total-price').text(0);
            },
            load_items: function(){
                this.shop_items_Holder.html(shop.shop_loader);
                $.ajax({
                    url: '/shop/myinventory',
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        if (!data.list.length){
                            shop.shop_items_Holder.html('<div style="text-align: center">Инвентарь пуст!</div>');
                            return;
                        }
                        data.list.forEach(function (zipItem) {
                            var item = shop.parce_item(zipItem);
                            shop.shop_items[item.id] = item;
                        });
                        shop.draw_items();
                    },
                    error: function () {
                        shop.clear_items();
                        shop.shop_items_Holder.html('<div style="text-align: center">Инвентарь пуст!</div>');
                    }
                });
            },
            new_item: function(zipItem){
                var item = new Object();
                item.id = zipItem.id;
                item.name = zipItem.name;
                item.price = zipItem.price;
                item.classid = zipItem.classid;
                item.shortexterior = zipItem.shortexterior;
                item.count = zipItem.count;
                item.exterior_all = zipItem.exterior_all;
                item.filter_rarity = zipItem.filter_rarity;
                item.rarity_all = zipItem.rarity_all;
                item.className = zipItem.className;
                item.ids = zipItem.ids;
                item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + zipItem.classid + '/101fx100f';
                return item;
            },
            buy_item: function(id){
                if(is_null(shop.shop_items[id])) return $.notify("Предмет не существует", { className: "error" });
                if(shop.shop_items[id].count <= 0) return $.notify("Предмет отсутствует", { className: "error" });
                var price = 0;
                if(shop.shiftPress){
                    price = shop.shop_items[id].price * shop.shop_items[id].count;
                } else {
                    price = shop.shop_items[id].price;
                }
                if(is_null(shop.shop_cart[id])){
                    var item = shop.new_item(shop.shop_items[id]);
                    if(!shop.shiftPress) item.count = 1;
                    shop.shop_cart[id] = item;
                } else {
                    if(shop.shiftPress){
                        shop.shop_cart[id].count += shop.shop_items[id].count;
                    } else {
                        shop.shop_cart[id].count += 1;
                    }
                }
                if(shop.shiftPress){
                    shop.shop_items[id].count = 0;
                } else {
                    shop.shop_items[id].count -= 1;
                }
                this.show_cart();
            },
            sell_cart: function(id){
                if(is_null(shop.shop_cart[id])) return $.notify("Предмет не существует", { className: "error" });
                if(shop.shop_cart[id].count <= 0) return;
                if(shop.shiftPress){
                    shop.shop_items[id].count += shop.shop_cart[id].count;
                    shop.shop_cart[id].count = 0;
                } else {
                    shop.shop_cart[id].count -= 1;
                    shop.shop_items[id].count += 1;
                }
                this.show_cart();
            },
            show_cart: function(){
                this.draw_cart();
                this.draw_items();
            },
            sell_cart_all: function(){
                var items_list = makeArray(this.shop_cart);
                items_list.forEach(function (item) {
                    shop.shiftPress = true;
                    shop.sell_cart(item.id);
                    shop.shiftPress = false;
                });
            },
            get_cart: function(){
                $.notify("Отправляем обмен", {className: "success"});
                var items_list = makeArray(this.shop_cart);
                var senditems = '';
                items_list.forEach(function (item) {
                    if(item.count > 0){
                        for (var i = 0; i < item.count; i++) {
                            senditems += item.ids[i] + ',';
                        }
                    }
                });
                $.ajax({
                    url: '/shop/sellitems',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        classids: senditems
                    },
                    success: function (data) {
                        if (data.success) {
                            $('#depTradeCode').text(data.msg);
                            $("#depUrl").attr("href", "https://steamcommunity.com/tradeoffer/" + data.tradeid);
                            $('#depModal').arcticmodal(); 
                        } else {
                            if (data.msg) $.notify(data.msg, {className: "error"});
                        }
                        shop.sell_cart_all();
                    },
                    error: function () {
                        $.notify("Произошла ошибка. Попробуйте еще раз", {
                            className: "error"
                        });
                    }
                });
            }
        });
        $(document).on('click', '#items-list .deposit-item', function () {
            shop.buy_item($(this).data('id'));
        });
        $(document).on('click', '#cart-list .deposit-item', function () {
            shop.sell_cart($(this).data('id'));
        });
        $(document).on('click', '#get-cart', function () { shop.get_cart() });
        $(document).on('change', '#exterior_all', shop.show_items);
        $(document).on('change', '#rarity_all', shop.show_items);
        $(document).on('change', '#sort_all', function(){shop.draw_items()});
        $('#searchInput, #priceFrom, #priceTo').keyup(shop.show_items);
        $(document).on('click', '.btn-inv', function () {
            shop.clear_items();
            shop.shop_items_Holder.html(shop.shop_loader);
            $.ajax({
                url: '/shop/inv_update',
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    shop.load_items();
                },
                error: function () {
                    $.notify("Произошла ошибка. Попробуйте еще раз", {
                        className: "error"
                    });
                }
            });
        });
        document.onkeyup = function checkKeycode(event){
            if(!event) var event = window.event;
            var keyShift = event.shiftKey;
            if(keyShift){
            shop.shiftPress = true;
            } else {
            shop.shiftPress = false;
            }
        }
        document.onkeydown = function checkKeycode(event){
            if(!event) var event = window.event;
            var keyShift = event.shiftKey;
            if(keyShift){
                shop.shiftPress = true;
            } else {
                shop.shiftPress = false;
            }
        }
        function is_null(data){
            if (data == null) return true;
            return false;
        }
        function makeArray(object){
            var array = $.map(object, function(value, index) {
                return [value];
            });
            return array;
        }
        shop.load_items();
    }
    if(checkUrl('/shop')){
        window.shop = ({
            shop_loader: '<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>',
            item_tpl: _.template($('#item-template').html()),
            shop_items: {},
            shop_items_Holder: $('#items-list'),
            shop_cart_sliden: false,
            shop_cart: {},
            shop_cart_price: 0,
            shop_cart_Holder: $('#cart-list'),
            shiftPress: false,
            parce_item: function(zipItem){
                var i = 0;
                zipItem = {
                    id: zipItem[i++],
                    count: zipItem[i++],
                    name: zipItem[i++],
                    priceCent: zipItem[i++],
                    classid: zipItem[i++],
                    exterior: zipItem[i++],
                    rarity: zipItem[i++],
                    rarity_text: zipItem[i++],
                    shortexterior: '',
                };
                if (zipItem.exterior == 'Прямо с завода') {
                    zipItem.shortexterior = 'FN';
                } else if (zipItem.exterior == 'Немного поношенное') {
                    zipItem.shortexterior = 'MW';
                } else if (zipItem.exterior == 'После полевых испытаний') {
                    zipItem.shortexterior = 'FT';
                    zipItem.exterior = 'После полевых';
                } else if (zipItem.exterior == 'Поношенное') {
                    zipItem.shortexterior = 'WW';
                } else if (zipItem.exterior == 'Закаленное в боях') {
                    zipItem.shortexterior = 'BS';
                } else if (zipItem.exterior == null) {
                    zipItem.shortexterior = '*';
                    zipItem.exterior = 'Не покрашено';
                } else {
                    zipItem.shortexterior = '*';
                    zipItem.exterior = 'Не покрашено';
                }
                if(zipItem.name.length > 35) {
                    zipItem.name = zipItem.name.substr(0, 34) + '...'
                }
                if(zipItem.exterior !== null) {
                    if (zipItem.exterior.length > 19) {
                        zipItem.exterior = zipItem.exterior.substr(0, 16) + '...'
                    }
                }
                var item_obj = {
                    id: zipItem.classid,
                    name: zipItem.name,
                    price: num(zipItem.priceCent),
                    classid: zipItem.classid,
                    shortexterior: zipItem.shortexterior,
                    count: zipItem.count,
                    exterior_all: zipItem.exterior,
                    filter_rarity: zipItem.rarity,
                    rarity_all: zipItem.rarity_text,
                    className: zipItem.rarity
                };
                item_obj.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item_obj.classid + '/101fx100f';
                item_obj.el = $(this.item_tpl(item_obj));
                return item_obj;
            },
            draw_items: function(){
                this.shop_items_Holder.children().replaceWith('');
                var items_list = makeArray(this.shop_items);
                var raritys = [], exteriors = [], temp_exterior = $('#exterior_all').val(), temp_rarity = $('#rarity_all').val();
                items_list.forEach(function (item) {
                    if(raritys.indexOf(item.rarity_all) == -1) raritys.push(item.rarity_all);
                    if(exteriors.indexOf(item.exterior_all) == -1) exteriors.push(item.exterior_all);
                });
                $('#rarity_all').children().replaceWith('');
                $('#rarity_all').append('<option value="">Все раритетности</option>');
                $('#exterior_all').children().replaceWith('');
                $('#exterior_all').append('<option value="">Любое качество</option>');
                raritys.forEach(function(item){
                    $('#rarity_all').append('<option value="' + item + '">' + item + '</option>');
                });
                exteriors.forEach(function(item){
                    $('#exterior_all').append('<option value="' + item + '">' + item + '</option>');
                });
                $('#exterior_all').val(temp_exterior);$('#rarity_all').val(temp_rarity);
                $('#items-total').text(_.reduce(items_list, function (memo, num) {
                    return memo + num.count;
                }, 0));
                $('#filter-total').text(_.reduce(items_list, function (memo, num) {
                    return memo + num.count;
                }, 0));
                if($('#sort_all').val() == 'desc'){
                    items_list.sort(function (a, b) {
                        return b.price - a.price
                    });
                } else {
                    items_list.sort(function (b, a) {
                        return b.price - a.price
                    });
                }
                items_list.forEach(function (item) {
                    item.el = $(shop.item_tpl(item));
                    shop.shop_items_Holder.append(item.el);
                });
                this.show_items();
            },
            draw_cart: function(){
                this.shop_cart_Holder.children().replaceWith('');
                var items_list = makeArray(this.shop_cart);
                items_list.sort(function (a, b) {
                    return b.price - a.price
                });
                var count = 0;
                var price = 0;
                items_list.forEach(function (item) {
                    if(item.count > 0) {
                        item.el = $(shop.item_tpl(item));
                        shop.shop_cart_Holder.append(item.el);
                        count += item.count;
                        price += item.price * item.count;
                    }
                });
                this.shop_cart_price = price;
                $('#cart-total').text(count);
                $('#cart-total-price').text(num(price));
            },
            show_items: function(){
                var args = [];
                var items_list = makeArray(shop.shop_items);
                ['exterior_all', 'rarity_all'].forEach(function(sel) {
                    var sorter = $('#' + sel).val();
                    if (sorter) {
                        var p = _.filter(items_list, function (item) {
                            var _exterior = item[sel];
                            return _exterior == sorter;
                        });
                        p = _.pluck(p, 'id');
                        args.push(p);
                    }
                });
                var from = parseFloat($('#priceFrom').val()) || 0;
                var to = parseFloat($('#priceTo').val()) || 10e10;
                if (to < from) to = 10e10;
                var p = _.filter(items_list, function (item) {
                    var _price = num(item.price);
                    return _price >= from && _price <= to;
                });
                p = _.pluck(p, 'id');
                args.push(p);
                var text = $('#searchInput').val().trim();
                text = text.replace('|', '\\|');
                var p = _.filter(items_list, function (item) {
                    return (new RegExp(text, 'i').test(item.name));
                });
                p = _.pluck(p, 'id');
                args.push(p);
                if(args.length){
                    var allItems = shop.shop_items_Holder.children('.deposit-item');
                    var s = _.intersection.apply(null, args);
                    var count = 0;
                    allItems.addClass('hidden');
                    s.forEach(function (id) {
                        if (shop.shop_items[id].count > 0){
                            shop.shop_items[id].el.removeClass('hidden');
                            count += shop.shop_items[id].count;
                        }
                    });
                    $('#filter-total').text(count);
                } else {
                    allItems.removeClass('hidden');
                }
            },
            clear_items: function(){
                this.shop_items = {};
                this.shop_cart = {};
                this.shop_cart_price = 0;
                this.shop_items_Holder.children().replaceWith('');
                this.shop_cart_Holder.children().replaceWith('');
                this.shop_items_Holder.html('');
                $('#items-total').text(0);
                $('#filter-total').text(0);
                $('#cart-total').text(0);
                $('#cart-total-price').text(0);
            },
            load_items: function(){
                this.shop_items_Holder.html(shop.shop_loader);
                $.ajax({
                    url: '/shop/items',
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        if (!data.list.length){
                            shop.clear_items();
                            shop.shop_items_Holder.html('<div style="text-align: center">Магазин пуст! Попробуйте позже!</div>');
                            return;
                        }
                        data.list.forEach(function (zipItem) {
                            var item = shop.parce_item(zipItem);
                            shop.shop_items[item.id] = item;
                        });
                        shop.draw_items();
                    },
                    error: function () {
                        shop.clear_items();
                        shop.shop_items_Holder.html('<div style="text-align: center">Магазин пуст! Попробуйте позже!</div>');
                    }
                });
            },
            add_new_items: function(data){
                //data = JSON.parse(data);
                if (!data.list.length) return;
                data.list.forEach(function (zipItem) {
                    var item = shop.parce_item(zipItem);
                    if(!is_null(shop.shop_items[item.id])){
                        shop.shop_items[item.id].count = item.count;
                    } else {
                        shop.shop_items[item.id] = item;
                    }
                });
                this.draw_items();
            },
            dell_items: function(data){
                //data = JSON.parse(data);
                data.list.forEach(function (id) {
                    if(!is_null(shop.shop_items[id])){
                        if(shop.shop_items[id].count > 0){
                            shop.shop_items[id].count -= 1;
                        } else {
                            if(!is_null(shop.shop_cart[id])){
                                if(shop.shop_cart[id].count > 0){
                                    shop.shop_cart[id].count -= 1;
                                }
                            }
                        }
                    }
                });
                this.show_cart();
            },
            new_item: function(zipItem){
                var item = new Object();
                item.id = zipItem.id;
                item.name = zipItem.name;
                item.price = zipItem.price;
                item.classid = zipItem.classid;
                item.shortexterior = zipItem.shortexterior;
                item.count = zipItem.count;
                item.exterior_all = zipItem.exterior_all;
                item.filter_rarity = zipItem.filter_rarity;
                item.rarity_all = zipItem.rarity_all;
                item.className = zipItem.className;
                item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + zipItem.classid + '/101fx100f';
                return item;
            },
            buy_item: function(id){
                if(is_null(shop.shop_items[id])) return $.notify("Предмет не существует", { className: "error" });
                if(shop.shop_items[id].count <= 0) return $.notify("Предмет отсутствует", { className: "error" });
                var price = 0;
                if(shop.shiftPress){
                    price = shop.shop_items[id].price * shop.shop_items[id].count;
                } else {
                    price = shop.shop_items[id].price;
                }
                if((price + shop.shop_cart_price) > USER_BALANCE) return $.notify("У вас недостаточно средств", { className: "error" });
                
                if(is_null(shop.shop_cart[id])){
                    var item = shop.new_item(shop.shop_items[id]);
                    if(!shop.shiftPress) item.count = 1;
                    shop.shop_cart[id] = item;
                } else {
                    if(shop.shiftPress){
                        shop.shop_cart[id].count += shop.shop_items[id].count;
                    } else {
                        shop.shop_cart[id].count += 1;
                    }
                }
                if(shop.shiftPress){
                    shop.shop_items[id].count = 0;
                } else {
                    shop.shop_items[id].count -= 1;
                }
                this.show_cart();
            },
            sell_cart: function(id){
                if(is_null(shop.shop_cart[id])) return $.notify("Предмет не существует", { className: "error" });
                if(shop.shop_cart[id].count <= 0) return;
                if(shop.shiftPress){
                    shop.shop_items[id].count += shop.shop_cart[id].count;
                    shop.shop_cart[id].count = 0;
                } else {
                    shop.shop_cart[id].count -= 1;
                    shop.shop_items[id].count += 1;
                }
                this.show_cart();
            },
            show_cart: function(){
                this.draw_cart();
                this.draw_items();
            },
            sell_cart_all: function(){
                var items_list = makeArray(this.shop_cart);
                items_list.forEach(function (item) {
                    shop.shiftPress = true;
                    shop.sell_cart(item.id);
                    shop.shiftPress = false;
                });
            },
            get_cart: function(){
                $.notify('Проверяем ваш запрос', {className: "success"});
                var senditems = [];
                var items_list = makeArray(this.shop_cart);
                items_list.forEach(function (item) {
                    if(item.count > 0) {
                        for (var i = 0; i < item.count; i++) {
                            senditems.push(item.id);
                        }
                    }
                });
                $.ajax({
                    url: '/shop/getcart',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        classids: senditems
                    },
                    success: function (data) {
                        if (data.success) {
                            $.notify(data.msg, {className: "success"});
                            updateBalance();
                            updateSlimit();
                        } else {
                            if (data.msg) $.notify(data.msg, {className: "error"});
                        }
                        shop.sell_cart_all();
                    },
                    error: function () {
                        $.notify("Произошла ошибка. Попробуйте еще раз", {
                            className: "error"
                        });
                    }
                });
            }
        });
        $(document).on('click', '#items-list .deposit-item', function () {
            shop.buy_item($(this).data('id'));
        });
        $(document).on('click', '#cart-list .deposit-item', function () {
            shop.sell_cart($(this).data('id'));
        });
        $(document).on('click', '#get-cart', function () { shop.get_cart() });
        $(document).on('change', '#exterior_all', shop.show_items);
        $(document).on('change', '#rarity_all', shop.show_items);
        $(document).on('change', '#sort_all', function(){shop.draw_items()});
        $('#searchInput, #priceFrom, #priceTo').keyup(shop.show_items);
        document.onkeyup = function checkKeycode(event){
            if(!event) var event = window.event;
            var keyShift = event.shiftKey;
            if(keyShift){
                shop.shiftPress = true;
            } else {
                shop.shiftPress = false;
            }
        }
        $(document).on('click', '#card_block', function () { 
            shop.shop_cart_sliden = !shop.shop_cart_sliden;
            if(shop.shop_cart_sliden){
                shop.shop_cart_Holder.slideDown();
            } else {
                shop.shop_cart_Holder.slideUp();
            }
        });
        document.onkeydown = function checkKeycode(event){
            if(!event) var event = window.event;
            var keyShift = event.shiftKey;
            if(keyShift){
                shop.shiftPress = true;
            } else {
                shop.shiftPress = false;
            }
        }
        function is_null(data){
            if (data == null) return true;
            return false;
        }
        function makeArray(object){
            var array = $.map(object, function(value, index) {
                return [value];
            });
            return array;
        }
        shop.load_items();
    }
    $('#chatInput').keypress(function(e) {
        if(!e.shiftKey && e.which == 13) {
            sendMessage($(this).val());
            $(this).val('');
            e.preventDefault();
        }
    });
    $('.chat-submit-btn').click(function(e) {
        sendMessage($('#chatInput').val());
        e.preventDefault();
    });
    $('#chatRules').click(function() {
        $('#chatRulesModal').arcticmodal();
    });
    $(window).scroll(function() {
        var scrollHeight = Math.max(
            document.body.scrollHeight, document.documentElement.scrollHeight,
            document.body.offsetHeight, document.documentElement.offsetHeight,
            document.body.clientHeight, document.documentElement.clientHeight
        );
        scrollHeight = scrollHeight - $(window).innerHeight();
        if($(this).scrollTop() != 0) {
            $('#toTop').fadeIn();
        } else {
            $('#toTop').fadeOut();
        }
        if($(this).scrollTop() != scrollHeight) {
            $('#toDown').fadeIn();
        } else {
            $('#toDown').fadeOut();
        }
    });
    $('#toTop').click(function() {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
    });
    $('#toDown').click(function() {
        var scrollHeight = Math.max(
            document.body.scrollHeight, document.documentElement.scrollHeight,
            document.body.offsetHeight, document.documentElement.offsetHeight,
            document.body.clientHeight, document.documentElement.clientHeight
        );
        scrollHeight = scrollHeight - $(window).innerHeight();
        $('body,html').animate({
            scrollTop: scrollHeight
        }, 800);
    });
    $(document).on("contextmenu", ".chatMessage", function(e) {
        if(e.ctrlKey) return;
        e.preventDefault();
        var steamid = $(this).attr("data-user");
        var name = $(this).attr("data-username");
        var $menu = $("#contextMenu");
        $menu.show().css({
            position: "absolute",
            left: getMenuPosition(e.clientX, 'width', 'scrollLeft'),
            top: getMenuPosition(e.clientY, 'height', 'scrollTop')
        }).off("click").on("click", "a", function(e) {
            var act = $(this).data("act");
            e.preventDefault();
            $menu.hide();
            if(act == 0) {
                var curr = $("#chatInput").val($("#chatInput").val() + steamid);
            } else if(act == 1) {
                var curr = $("#chatInput").val($("#chatInput").val() + name);
            } else if(act == 2) {
                sendMoney(steamid);
            } else if(act == 3) {
                window.open('https://steamcommunity.com/profiles/' + steamid + '/', '_blank');
            } else if(act == 4) {
                window.open('/user/' + steamid, '_blank');
            } else if(act == 5) {
                window.open('/admin/user/' + steamid, '_blank');
            }
            $("#chatInput").focus();
        });
    });
    $(document).on("click", function() {
        $("#contextMenu").hide();
    });

    if(checkUrl('/')) if(have_gift) $('#giftModal').arcticmodal(); 
    $('.profile-balance').tooltip({
        html: true,
        trigger: 'hover',
        delay: {
            show: 500,
            hide: 500
        },
        title: function() {
            return '<div class="tooltip-title"><span>Пополнить баланс</span></div>';
        }
    });
    sound_status = true;
    if($.cookie('sound_status') == 'false') {
        $('.sound_off').hide();
        $('.sound_on').show();
        sound_status = false;
    }
    $('.sound_on').click(function() {
        $(this).hide();
        $('.sound_off').show();
        $.cookie('sound_status', 'true', {
            expires: 5,
            path: '/',
        });
        sound_status = true;
        $.notify('Звук включен', {
            clickToHide: 'true',
            autoHide: "false",
            className: "success"
        });
    });
    $('.sound_off').click(function() {
        $(this).hide();
        $('.sound_on').show();
        $.cookie('sound_status', 'false', {
            expires: 5,
            path: '/',
        });
        sound_status = false;
        $.notify('Звук выключен', {
            clickToHide: 'true',
            autoHide: "false",
            className: "success"
        });
    });
    snowStorm.start();
    if($.cookie('snow_status') == 'false') {
        $('.snow_off').hide();
        $('.snow_on').show();
        snowStorm.stop();
        snowStorm.freeze();
    }
    $('.snow_on').click(function() {
        $(this).hide();
        $('.snow_off').show();
        $.cookie('snow_status', 'true', {
            expires: 5,
            path: '/',
        });
        $.notify('Снег включен', {
            clickToHide: 'true',
            autoHide: "false",
            className: "success"
        });
        snowStorm.show();
        snowStorm.resume();
    });
    $('.snow_off').click(function() {
        $(this).hide();
        $('.snow_on').show();
        $.cookie('snow_status', 'false', {
            expires: 5,
            path: '/',
        });
        $.notify('Снег выключен', {
            clickToHide: 'true',
            autoHide: "false",
            className: "success"
        });
        snowStorm.stop();
        snowStorm.freeze();
    });
    load_page();
    if($.cookie('vk_post') != 'true') {
        VK.Widgets.CommunityMessages("vk_community_messages", 133906356, {widgetPosition: "left",expandTimeout: "5000",tooltipButtonText: "У вас есть вопросы? Задайте их нам."});
        $.cookie('vk_post', 'true', {expires: 0.1,path: '/',});
    } else {
        VK.Widgets.CommunityMessages("vk_community_messages", 133906356, {widgetPosition: "left",tooltipButtonText: "У вас есть вопросы? Задайте их нам."});
    }
    $('a[href="' + document.location.pathname + '"]').parent().addClass('active');
    $('.deposit-item:not(.card)').tooltip({
        container: 'body'
    });
    $('[data-toggle="popover"]').popover({
        "container": "body"
    });
    $('.close-this-msg').click(function (e) {
        $(this).parent('.msg-wrap').slideUp();
    });
    $(document).on('click', '.no-link', function() {
        $('#linkBlock').slideDown();
        return false;
    });
    $(document).on('click', '.tooltip-btn.level', function() {
        $('.profile-level').tooltip('hide');
        $('#level-popup').arcticmodal();
    });
    $(document).on('click', '.tooltip-btn.card', function() {
        $('.deposit-item.card').tooltip('hide');
        $('#card-popup').arcticmodal();
    });
    $(document).on('click', '.tooltip-btn.ticket', function() {
        $('.ticket-number').tooltip('hide');
        $('#ticket-popup').arcticmodal();
    });
    $(document).on('click', '#user-level-btn', function() {
        $('.level-badge').tooltip('hide');
        $('#level-popup').arcticmodal();
    });
    $('.tooltip').remove();
    $('.ticket-number').tooltip({
        html: true,
        trigger: 'hover',
        delay: {
            show: 500,
            hide: 3000
        },
        title: function() {
            var text = $(this).data('old-title');
            return '<div class="tooltip-title"><span>' + text + '</span></div>';
        }
    });
    $('.deposit-item:not(.card)').tooltip({
        container: 'body',
        delay: {
            show: 50,
            hide: 200
        }
    });
    $('.deposit-item.card').each(function() {
        var that = $(this);
        that.data('old-title', that.attr('title'));
        that.attr('title', null);
        that.tooltip({
            html: true,
            trigger: 'hover',
            delay: {
                show: 50,
                hide: 200
            },
            title: function() {
                var text = $(this).data('old-title');
                return '<span class="tooltip-title card">' + text + '</span>';
            }
        });
    });
    $('.save-trade-link-input')
        .keypress(function(e) {
            if(e.which == 13) $(this).next().click()
        })
        .on('paste', function() {
            var that = $(this);
            setTimeout(function() {
                that.next().click();
            }, 0);
        });
    $('.save-trade-link-input-btn').click(function() {
        var that = $(this).prev();
        $.ajax({
            url: '/settings/save',
            type: 'POST',
            dataType: 'json',
            data: {
                trade_link: $(this).prev().val()
            },
            success: function(data) {
                if(data.status == 'success') {
                    $('#linkBlock').slideUp();
                    $('.no-link').removeClass('no-link');
                    if(data.msg) return $.notify(data.msg, 'success');
                }
                if(data.msg) $.notify(data.msg, 'error');
            },
            error: function() {
                ajaxError();
            }
        });
        return false;
    });
    $('.tooltip').remove();
    $('.current-user').tooltip({
        container: 'body'
    });
    $(document).on('click', '#msb', function() {
        if($('#mssum').val() != '') {
            if(num($('#mssum').val()) > 0) {
                $.post('/send', {
                    steamid: SM_ID,
                    sum: num($('#mssum').val())
                }, function(data) {
                    updateBalance();
                    sendUpdate();
                    return $.notify(data.text, data.type);
                });
            } else {
                return $.notify('Укажите сумму.', 'error');
            }
        } else {
            return $.notify('Укажите сумму', 'error');
        }
    });
    update_chat();
    toggleChat();
    updateChatMargin();
    updateBackground();
    updateScrollbar();
});
$(document).on('click', '#coin_bet', function () {
    $.post('/coin/nbet', {
        sum: $('#coin_sum').val()
    }, function(data) {
        if(data.type == 'success'){
            USER_BALANCE = num(num(USER_BALANCE) - num($('#coin_sum').val()));
            $('.userBalance').text(USER_BALANCE);
        }
        return $.notify(data.text, data.type);
    });
});
$(document).on('click', '.vote', function() {
    var that = $(this);
    $.ajax({
        url: '/ajax',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'voteUser',
            id: $(this).data('profile')
        },
        success: function(data) {
            if(data.status == 'success') {
                $('#myProfile').find('.votes').text(data.votes || 0);
            } else {
                if(data.msg) that.notify(data.msg, {
                    position: 'bottom middle',
                    className: "error"
                });
            }
        },
        error: function() {
            that.notify("Произошла ошибка. Попробуйте еще раз", {
                position: 'bottom middle',
                className: "error"
            });
        }
    });
});
$.notify.addStyle('custom', {html: "<div>\n<span data-notify-text></span>\n</div>"});
$.notify.defaults({style: "custom"});
$(document).on('mouseenter', '.iusers, .iskins', function() {$(this).tooltip('show');});
$(document).on('click', '#showUsers, #showItems', function() {
    if($(this).is('.active')) return;
    $('#showUsers, #showItems').removeClass('active');
    $(this).addClass('active');
    $('#usersChances .users').toggle();
    $('#usersChances .items').toggle();
    updateScrollbar();
});
$('#usersChances').hover(function() {
    var block = $('#showUsers').is('.active') ? $('.current-chance-block.users') : $('.current-chance-block.items');
    var min = $('#showUsers').is('.active') ? 10 : 9;
    if(block.find('.current-chance-wrap').children().length > min) $('.arrowscroll').show();
}, function() {
    $('.arrowscroll').hide();
});
$('.arrowscroll').click(function() {
    var block = $('#showUsers').is('.active') ? $('.current-chance-block.users') : $('.current-chance-block.items');
    var direction = $(this).is('.left') ? '-' : '+';
    block.stop(true, false).animate({scrollLeft: direction + "=250"});
});
var declineTimeout,
    timerStatus = true,
    ngtimerStatus = true,
    onlineList = [];
var centrifuge = new Centrifuge({
    url: 'ws://beta.mh00.net:8000/connection/websocket',
    user: USER_ID,
    timestamp: CENT_TIME,
    token: CENT_TIKEN
});
centrifuge.connect();
centrifuge.on('connect', function(context) {});
centrifuge.subscribe("test", function(message) {
    console.log(message);
});
centrifuge.subscribe("update", function(message) {
    updateData(message);
});
var fupdate = false;
centrifuge.subscribe("update#" + USER_ID, function(message) {
    if( !fupdate ){
        updateData(message);
        fupdate = true;
    }
});
centrifuge.subscribe("status", function(message) {
    var data = message.data;
    $('#statBot').removeAttr('title');
    $('#statBot').removeAttr('data-original-title');
    $('#statBot').attr('title', data.rus);
    $('#statBot').attr('data-original-title', data.rus);
    $('#statBot').toggleClass(data.stat);
});
centrifuge.subscribe("chat_add", function(message) {
    var data = message.data;
    updateChatMargin();
    addMsg(data);
});
centrifuge.subscribe("chat_del", function(message) {
    var data = message.data;
    updateChatMargin();
    delMsg(data);
});
if(USER_ID != 76561197960265728) {
    centrifuge.subscribe("notification#" + USER_ID, function(message) {
        var data = message.data;
        updateBalance();
        $.notify(data.message, {
            clickToHide: 'true',
            autoHide: "false",
            className: "success"
        });
    });
}
centrifuge.subscribe("queue", function(message) {
    var data = message.data;
    var n = data.indexOf(USER_ID);
    if(n !== -1) {
        $.notify('Ваш депозит обрабатывается. Вы ' + (n + 1) + ' в очереди.', {
            clickToHide: 'false',
            autoHide: "false",
            className: "success"
        });
        $('#count_trades').html(data.length);
    } else {
        $('#count_trades').html(data.length);
    }
});
if(checkUrl('/')) {
    centrifuge.subscribe("newDeposit", function(message) {
        var data = message.data;
        $('#roundFinishBlock').hide();
        playBetSound(data.betprice);
        if($('#deposits').children(".deposits-container").first().attr('id') != ('bet_' + data.betId)){
            $('#deposits').prepend(data.html);
        } else {
            $('#bet_' + data.betId).remove();
            $('#deposits').prepend(data.html);
        }
        if(data.cc) {
            $('.current-chance-wrap').html('');
            $('.current-chance-wrap').prepend(data.cc);
        }
        $('#roundBank').html(Math.round(data.gamePrice) + ' <span class="money" style="color: #b3e5ff;">руб</span>');
        $('title').text(Math.round(data.gamePrice) + ' р. | CSGF.RU');
        $('.item-bar-text').html('<span>' + data.itemsCount + '<span style="font-weight: 100;"> / </span>' + MAX_ITEMS + '</span>' + n2w(data.itemsCount, [' предмет', ' предмета', ' предметов']));
        $('.item-bar').css('width', ((data.itemsCount/MAX_ITEMS)*100) + '%');
        $('.deposit-item').tooltip({container: 'body',placement: 'top'});
        html_chances = '';
        data.chances.forEach(function(info) {
            if(USER_ID == info.steamid64) {
                $('#myItemsCount').html(info.items + '<span style="font-size: 12px;">' + n2w(info.items, [' предмет', ' предмета', ' предметов']) + '</span>');
                $('#myChance').text(info.chance + '%');
            }
            $('.id-' + info.steamid64).text(info.chance + '%');
            var style = '';
            if (info.vip) style = 'style="border: 1px dashed #F9FF2F;"';
            html_chances += '<div class="current-user" title="' + info.username + '"><a class="img-wrap" href="/user/' + info.steamid64 + '" target="_blank"><img ' + style + ' src="' + info.avatar + '" /></a><div class="chance">' + info.chance + '%</div></div>';
        });
        $('#usersChances .users .current-chance-wrap').html(html_chances);
        $('#usersChances').show();
        $('.tooltip').remove();
        $('.current-user').tooltip({container: 'body'});
        CSGF.initTheme();
        $('.ticket-number').tooltip({
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
        updateChatMargin();
        updateBackground();
    });
    if(USER_ID != 76561197960265728) {
        centrifuge.subscribe("view_bet#" + USER_ID, function(message) {
            var data = message.data;
            $('#view_deposits').html(data.html);
            $('#vd').slideDown();
            setTimeout(function() {
                $('#view_deposits').html('');
                $('#vd').slideUp();
            }, 5000);
        });
    }
    centrifuge.subscribe("forceClose", function(message) {
        $('.forceClose').removeClass('msgs-not-visible');        
    });
    centrifuge.subscribe("timer", function(message) {
        var time = message.data;
        var audio_notice = new Audio('/assets/sounds/notice.mp3');
        $('#gameTimer .countMinutes').text(lpad(Math.floor(time / 60), 2));
        $('#gameTimer .countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
        if(time == 30 && sound_status) audio_notice.play();
    });
    centrifuge.subscribe("slider", function(message) {
        var data = message.data;
        if(data.time != null) $('#newGameTimer .countSeconds').text(lpad(data.time, 2));
        if(ngtimerStatus) {
            ngtimerStatus = false;
            $('#roundFinishBlock').hide();
            var users = data.userchanses;
            var a_this_scrol = getRandomInt(112, 165)
            users = mulAndShuffle(users, Math.ceil(180 / users.length));
            users[a_this_scrol] = data.winner;
            var j = 0, html = '';
            users.forEach(function(i) {
                if(j != a_this_scrol) {
                    html += '<li><img src="' + i.avatar + '"></li>';
                } else {
                    html += '<li id="li_winner_112"><img style="" id="img_winner_112" src="' + i.avatar + '"><div class="chance" id="div_winner_112" style="display:none">Winner</div></li>';
                }
                j++;
            });
            $('#usersCarousel').html(html);
            $('#barContainer').hide();
            $('#usersCarouselConatiner').show();
            if(data.showCarousel) {
                $('#depositButtonsBlock').slideUp();
            } else {
                $('#depositButtonsBlock').hide();
            }
            $('#winnerInfo').show();
            fillWinnerInfo(data);
            $('#roundFinishBlock .number').text(data.round_number);
            $('#roundFinishBlock .date').html(data.date + '<span>' + data.date_hours + '</span>');
            var this_scrol = (a_this_scrol * 75) + 37 - 525;
            if(getRandomInt(1, 5) == 1) {
                var scrollmarl = getRandomInt(1, 10)
            } else {
                var scrollmarl = getRandomInt(10, 25)
            }
            if(getRandomInt(1, 2) == 1) {
                var scrollmar = -this_scrol + scrollmarl;
            } else {
                var scrollmar = -this_scrol - scrollmarl;
            }
            if(data.showSlider) {
                var audio = new Audio('/assets/sounds/scroll.mp3');
                if(sound_status) audio.play();
                $('#usersCarousel').css("transform", "translate(" + scrollmar + "px)").css('transition-duration', (1000 * (data.time - 10)) + 'ms');
                setTimeout(function(){
                    $('#li_winner_112').css({'height': '70px'});
                    $('#div_winner_112').slideDown();
                    $('#li_winner_112').addClass("rotate");
                    $('#usersCarouselConatiner').css({'z-index': '107'});
                    $('#winnerInfo .winner-info-holder').slideDown();
                    $('#roundFinishBlock').show();
                    if(data.winner.steamid64 == USER_ID) {
                        $.notify('Поздравляем с победой', {
                            clickToHide: 'true',
                            autoHide: "false",
                            className: "success"
                        });
                        $("#toTrade").attr("href", 'http://steamcommunity.com/profiles/' + USER_ID + '/tradeoffers/');
                        $('#toTrade').fadeIn();
                        setTimeout(function() {$('#toTrade').fadeOut();}, 30000);
                    }
                    $('#usersCarousel').css("transform", "translate(" + (-this_scrol) + "px)").css('transition-duration', '2000ms');
                }, 1000 * (data.time - 10));
            } else {
                $('#usersCarousel').css("transform", "translate(" + scrollmar + "px)").css('transition-duration', '1000ms');
                setTimeout(function(){
                    $('#li_winner_112').css({'height': '70px'});
                    $('#div_winner_112').slideDown();
                    $('#li_winner_112').addClass("rotate");
                    $('#usersCarouselConatiner').css({'z-index': '107'});
                    $('#winnerInfo .winner-info-holder').slideDown();
                    $('#roundFinishBlock').show();
                    $('#usersCarousel').css("transform", "translate(" + (-this_scrol) + "px)").css('transition-duration', '1000ms');
                }, 1000);
            }
        }
    });
    centrifuge.subscribe("newGame", function(message) {
        var data = message.data;
        if(USER_ID != 76561197960265728) updateBalance();
        //$('html, body').animate({'scrollTop': '0'}, 'slow');
        $('#usersCarouselConatiner').css({'z-index': '0'});
        var audio = new Audio('/assets/sounds/newgame.mp3');
        if(sound_status) audio.play();
        $('#usersCarousel').css("transform", "translate(0px)").css('transition-duration', '0ms');
        $('#usersChances .users .current-chance-wrap').html('');
        $('#usersChances .items .current-chance-wrap').html('');
        $('#usersChances').hide();
        $('#deposits').html('');
        $('#myItemsCount').html('0 <span style="font-size: 12px;"> предметов</span>');
        $('#myChance').text('0%');
        $('#roundId').text(data.id);
        $('#roundBank').html('0 <span class="money" style="color: #b3e5ff;">руб</span>');
        $('#hash').text(data.hash);
        $('.item-bar-text').html('<span>0<span style="font-weight: 100;"> / </span>' + MAX_ITEMS +'</span> предметов');
        $('.item-bar').css('width', '0%');
        $('#roundFinishBlock').hide();
        $('#barContainer').show();
        $('#usersCarouselConatiner').hide();
        $('#depositButtonsBlock').show();
        $('#winnerInfo').hide();
        $('#winnerInfo .winner-info-holder').hide();
        $('#gameTimer .countMinutes').text('02');
        $('#gameTimer .countSeconds').text('00');
        $('title').text('0 р. | CSGF.RU');
        $('#roundStartBlock #date').html(data.created_at);
        ngtimerStatus = true;
        updateBackground();
        updateChatMargin();
    });
    centrifuge.subscribe("depositDecline", function(message) {
        var data = message.data;
        if(data.user == USER_ID) {
            clearTimeout(declineTimeout);
            declineTimeout = setTimeout(function() {
                $('#errorBlock').slideUp();
            }, 1000 * 10)
            $('#errorBlock p').text(data.msg);
            $('#errorBlock').slideDown();
        }
    });
    centrifuge.subscribe("gifts", function(message) {
        var data = message.data;
        $('#last-gout-block').prepend('<img class="giftwinner" title="' + data.game_name + ' | ' + data.store_price + '"  style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="' + data.user_ava + '" class="scale-in">');
        $('.giftwinner').tooltip({
            html: true,trigger: 'hover',delay: {show: 500,hide: 500},
            title: function() {
                var text = $(this).data('old-title');
                return '<div class="tooltip-title"><span>' + text + '</span></div>';
            }
        });
        if(data.steamid == USER_ID) {
            $('#game_name').text(data.game_name);
            $('#store_price').text(data.store_price);
            $('#giftModal').arcticmodal(); 
        }
        $('#gifts_' + data.id).fadeOut();
        var html = '<tr id="gifts_' + data.id + '"><td><s style="color: rgba(154, 154, 154, 0.5);">' + data.game_name + '</s></td><td><s style="color: rgba(154, 154, 154, 0.5);">' + data.store_price + '</s></td></tr>';
        setTimeout(function(){
            $('#bgifts').append(html);
        },500);
    });
    
}
if(checkUrl('/coin')) {
    centrifuge.subscribe("coin_new", function(message) {
        var data = message.data;
        var audio = new Audio('/assets/sounds/coin/coinnew.ogg');
        if(sound_status) audio.play();
        $('#cointable').append($(coin_tpl(data)));
    });
    centrifuge.subscribe("coin_scroll", function(message) {
        var data = message.data;
        var audio = new Audio('/assets/sounds/coin/coinplay.ogg');
        if(sound_status) audio.play();
        $("#cointable #coin_" + data.id + " #second #user-name").text(data.name);
        $("#cointable #coin_" + data.id + " #second #user-ava").attr("src", data.ava);
        $("#cointable #coin_" + data.id + " #f1 .front #user-ava").attr("src", data.ava);
        $("#cointable #coin_" + data.id + " button").fadeOut();
        setTimeout(function(){
            $("#cointable #coin_" + data.id + " #f1").fadeIn(), setTimeout(function() {
                $("#cointable #coin_" + data.id + " #f1").addClass('flip');
            }, 500);
            setTimeout(function(){
                $("#cointable #coin_" + data.id + " #f2 .back #user-ava").attr("src", data.ava);
                $("#cointable #coin_" + data.id + " #f2").fadeIn(), setTimeout(function() {
                    $("#cointable #coin_" + data.id + " #f2").addClass('flip');
                }, 500);
                setTimeout(function(){
                    $("#cointable #coin_" + data.id + " #f3 .front #user-ava").attr("src", data.lava);
                    $("#cointable #coin_" + data.id + " #f3 .back #user-ava").attr("src", data.wava);
                    $("#cointable #coin_" + data.id + " #f3").fadeIn(), setTimeout(function() {
                        $("#cointable #coin_" + data.id + " #f3").addClass('flip');
                        setTimeout(function() {
                            if(data.wava == data.ava){
                                $("#cointable #coin_" + data.id + " #second #user-name").css('color', '#d1ff78');
                            } else {
                                $("#cointable #coin_" + data.id + " #first #user-name").css('color', '#d1ff78');
                            }
                            if(USER_ID == data.user_id){
                                USER_BALANCE = num(num(USER_BALANCE) + num(num(num($("#cointable #coin_" + data.id + " #sum").text()) * 2)*0.9));
                                $('.userBalance').text(USER_BALANCE);
                            }
                        }, 5000);
                    }, 500);
                    setTimeout(function() {
                        $("#cointable #coin_" + data.id).fadeOut(), setTimeout(function() {
                            $("#cointable #coin_" + data.id).remove();
                        }, 500);
                    }, 10000);
                }, 1000);
            }, 1000);
        }, 500);
    });
}
if(checkUrl('/dice')){
    centrifuge.subscribe("dice", function(message) {
        var data = message.data;
        setTimeout(function(){
            dice.addLastGame(data);
        }, 1500);
    });
}
if(checkUrl('/shop')){
    centrifuge.subscribe("addShop", function(message) {
        var data = message.data;
        shop.add_new_items(data);
    });
    centrifuge.subscribe("delShop", function(message) {
        var data = message.data;
        shop.dell_items(data);
    });
}
if(checkUrl('/out')) {
    centrifuge.subscribe("out_new", function(message) {
        var data = message.data;
        $('#last-gout-block').prepend('<img style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="' + data + '" class="scale-in">');
    });
}
if(checkUrl('/double')) {
    var ngtimerStatus = true,
        sld = false;
    centrifuge.subscribe("ngdouble", function(message) {
        var data = message.data;
        $('#ddr').slideUp();
        $('#ddg').slideUp();
        $('#ddb').slideUp();
        setTimeout(function() {
            $('#ddr').html('');
            $('#ddg').html('');
            $('#ddb').html('');
            $('#ddr').slideDown();
            $('#ddg').slideDown();
            $('#ddb').slideDown();
        }, 250);
        ngtimerStatus = true;
        if(USER_ID != 76561197960265728) updateBalance();
        $('#roundId').text(data);
        $('#tbetcr').text('0');
        $('#tbetcg').text('0');
        $('#tbetcb').text('0');
        $('#mybetr').css('font-weight', 'initial');
        $('#mybetg').css('font-weight', 'initial');
        $('#mybetb').css('font-weight', 'initial');
        $('#tbetr').css('font-weight', 'initial');
        $('#tbetg').css('font-weight', 'initial');
        $('#tbetb').css('font-weight', 'initial');
        $('#mybetr').text('0');
        $('#mybetg').text('0');
        $('#mybetb').text('0');
        $('#roundBank').text('0');
        $('#gameTimer .countSeconds').text('35');
        $('.item-bar').css('width', '0%');
        $('#double_txt').text('');
        var audio = new Audio('/assets/sounds/newgame.mp3');
        if(sound_status) audio.play();
        $('#barContainer').slideUp();
        $('#dbuttons').slideDown();
        sld = false;
    });
    centrifuge.subscribe("dbtimer", function(message) {
        var time = message.data;
        if(!sld) {
            $('#barContainer').slideDown();
            $('#dbuttons').slideUp();
            sld = true;
        }
        $('.item-bar').css('width', ((35 - time) / 35) * 100 + '%');
        var audio_scroll = new Audio('/assets/sounds/timer.mp3');
        var audio_notice = new Audio('/assets/sounds/notice.mp3');
        $('#gameTimer .countMinutes').text(lpad(Math.floor(time / 60), 2));
        $('#gameTimer .countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
        if(time < 35 && sound_status) audio_scroll.play();
        if(time == 35 && sound_status) audio_notice.play();
    });
    centrifuge.subscribe("doubleslider", function(message) {
        var data = message.data;
        if(ngtimerStatus) {
            $('.item-bar').css('width', '100%');
            $('#gameTimer .countSeconds').text('00');
            $('#double_txt').text('Крутим шарманку...');
            ngtimerStatus = false;

            function getRandomInt(min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            if(getRandomInt(1, 5) == 1) {
                var scrollmarl = getRandomInt(1, 10)
            } else {
                var scrollmarl = getRandomInt(10, 25)
            }
            if(getRandomInt(1, 2) == 1) {
                var scrollmarg = scrollmarl - data.scrollpx - 1125;
            } else {
                var scrollmarg = -scrollmarl - data.scrollpx - 1125;
            }
            var scrollmar = (-1125 * 10) + scrollmarg;
            var easetype = 'easeOutCirc';
            if(data.showSlider) {
                var audio = new Audio('/assets/sounds/scroll.mp3');
                if(sound_status) audio.play();
                $('#DoubleCarousel').animate({
                    marginLeft: scrollmar
                }, 1000 * 10, easetype, function() {
                    if(USER_ID != 76561197960265728) updateBalance();
                    $('#double_txt').text('Выпало: ' + data.num);
                    if(data.win == 1) {
                        $('#mybetr').css('font-weight', 'bold');
                        $('#mybetr').text('+' + ($('#mybetr').text()) * 2);
                        $('#mybetg').text('-' + $('#mybetg').text());
                        $('#mybetb').text('-' + $('#mybetb').text());
                        $('#tbetr').css('font-weight', 'bold');
                        $("#tbetcr").text('+' + ($('#tbetcr').text()) * 2),
                            $('#tbetcg').text('-' + $('#tbetcg').text());
                        $('#tbetcb').text('-' + $('#tbetcb').text());
                    } else if(data.win == 2) {
                        $('#mybetg').css('font-weight', 'bold');
                        $('#mybetg').text('+' + ($('#mybetg').text()) * 12);
                        $('#mybetr').text('-' + $('#mybetr').text());
                        $('#mybetb').text('-' + $('#mybetb').text());
                        $('#tbetg').css('font-weight', 'bold');
                        $("#tbetcg").text('+' + ($('#tbetcg').text()) * 12),
                            $('#tbetcr').text('-' + $('#tbetcr').text());
                        $('#tbetcb').text('-' + $('#tbetcb').text());
                    } else if(data.win == 3) {
                        $('#mybetb').css('font-weight', 'bold');
                        $('#mybetb').text('+' + ($('#mybetb').text()) * 2);
                        $('#mybetg').text('-' + $('#mybetg').text());
                        $('#mybetr').text('-' + $('#mybetr').text());
                        $('#tbetb').css('font-weight', 'bold');
                        $("#tbetcb").text('+' + ($('#tbetcb').text()) * 2),
                            $('#tbetcr').text('-' + $('#tbetcr').text());
                        $('#tbetcg').text('-' + $('#tbetcg').text());
                    }
                    $('#lastGames').html(data.ehtml);
                    $('#DoubleCarousel').css('margin-left', scrollmarg);
                    $('#DoubleCarousel').animate({
                        marginLeft: scrollmarg
                    }, 1000 * 5, easetype, function() {
                        $('#DoubleCarousel').animate({
                            marginLeft: -50 - 1125
                        }, 1000 * 5, easetype, function() {
                            $('#DoubleCarousel').css('margin-left', -50);
                        });
                    });
                });
            }
        }
    });
    centrifuge.subscribe("nbdouble", function(message) {
        var data = message.data;
        if(data.type == 1) {
            $('#tbetcr').text(data.tbp);
            $('#ddr').prepend(data.html);
            if(data.userid == USER_ID) {
                $('#mybetr').text(parseInt($('#mybetr').text()) + data.price);
            }
        }
        if(data.type == 2) {
            $('#tbetcg').text(data.tbp);
            $('#ddg').prepend(data.html);
            if(data.userid == USER_ID) {
                $('#mybetg').text(parseInt($('#mybetg').text()) + data.price);
            }
        }
        if(data.type == 3) {
            $('#tbetcb').text(data.tbp);
            $('#ddb').prepend(data.html);
            if(data.userid == USER_ID) {
                $('#mybetb').text(parseInt($('#mybetb').text()) + data.price);
            }
        }
        $('#roundBank').text(parseInt($('#tbetcr').text()) + parseInt($('#tbetcg').text()) + parseInt($('#tbetcb').text()));
        var audio = new Audio('/assets/sounds/betLow.mp3');
        if(sound_status) audio.play();
    });
}
var conline = centrifuge.subscribe("online", function(message) {
    var data = message.data;
    $('#count_online').html(data);
});
/*.on("join", function(message) {
    this.presence().then(function(message) {
        var online = $.map(message.data, function(value, index) {return [value];});
        console.log('Online: ' + online.length);
    }, function(err) {});
}).on("leave", function(message) {
    this.presence().then(function(message) {
        var online = $.map(message.data, function(value, index) {return [value];});
        console.log('Online: ' + online.length);
    }, function(err) {});
}).presence().then(function(message) {
    var online = $.map(message.data, function(value, index) {return [value];});
    console.log('Online: ' + online.length);
}, function(err) {});*/