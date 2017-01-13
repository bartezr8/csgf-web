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
    updateChatScroll();
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
    var
        res = [],
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
function updateChatMargin() {
    var dadCont = $('.dad-container'),
        dadContHeight = dadCont.innerHeight(),
        windowHeight = $(window).innerHeight();
    if(dadContHeight <= windowHeight) $('#chatContainer').css({"margin-top": 0});
}
function toggleChat() {
    //Config variable
    var mainContainer = $('.main-container'),
        dadContainer = $('.dad-container'),
        chatBody = $('#chatBody'),
        chatHeader = $('#chatHeader'),
        chatClose = $('#chatClose'),
        chatContainer = $('#chatContainer'),
        chatScroll = $('#chatScroll'),
        viewPortHeight = $(window).innerHeight(),
        viewPortWidth = $(window).innerWidth();
    chatContainer.css({
        "height": viewPortHeight
    });
    $(window).resize(function() {
        viewPortHeight = $(window).innerHeight();
        chatContainer.css({
            "height": viewPortHeight
        });
    });
    $('body').append(chatHeader);
    if(getCookie('chat') !== '0') {
        mainContainer.addClass('with-chat').find('.dad-container').addClass('with-chat');
        chatContainer.show();
    } else {
        chatHeader.fadeIn();
    }
    setTimeout(updateChatScroll, 0);
    var timerChatCheck = setInterval(updateChatScroll, 1000);
    chatScroll.perfectScrollbar();
    chatClose.on('click', function(e) {
        e.preventDefault();
        document.cookie = "chat=0";
        chatContainer.animate({
            width: 'toggle'
        }, 400, function() {
            $('meta[name=viewport]').attr('content', 'width=1050');
            mainContainer.toggleClass('with-chat').find('.dad-container').toggleClass('with-chat');
            chatHeader.fadeIn();
        });
    });
    //Open chat
    chatHeader.on('click', function(e) {
        e.preventDefault();
        $(this).fadeOut();
        document.cookie = "chat=1";
        mainContainer.removeClass('big-padding');
        //Change viewport
        $('meta[name=viewport]').attr('content', 'width=1280');
        mainContainer
            .toggleClass('with-chat')
            .find('.dad-container')
            .toggleClass('with-chat');
        chatContainer.animate({
            width: 'toggle'
        }, 400);
    });
    //Scroll event, emulation fixed block;
    $(window).bind('scroll.chatScroll', function() {
        var dadHeight = dadContainer.innerHeight(),
            chatContHeight = chatContainer.innerHeight(),
            scrollTop = $(this).scrollTop();
        if(dadHeight > chatContHeight) {
            chatContainer.css({
                "margin-top": scrollTop
            });
        } else {
            chatContainer.css({
                "margin-top": 0
            });
        }
        updateChatScroll();
    });
    //If user screen size < 1360 hidden chat
    if(viewPortWidth < 1360) {
        chatClose.trigger('click');
    }
}
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
function updateChatScroll() {
    var chatScroll = $('#chatScroll'),
        windowHeight = $(window).innerHeight(),
        chatInput = $('#chatInput'),
        chatForm = $('.chat-form'),
        chatNotLogged = $('#notLoggedIn'),
        chatPrompt = $('.chat-prompt'),
        chatHeight = windowHeight;
    if(chatInput.length) {
        chatHeight = chatHeight - chatForm.innerHeight() - 18;
    } else {
        chatHeight = chatHeight - chatNotLogged.innerHeight() - 15;
    }
    if(chatPrompt.length) chatHeight = chatHeight - chatPrompt.innerHeight();
    chatScroll.css({'height': chatHeight});
}
function delete_message(id) {
    $.ajax({
        url: '/delmsg',
        type: 'POST',
        dataType: 'json',
        data: {
            id: id
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
            //update_chat();
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
    $('#chat_msg_' + message.id).remove();
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
$(function() {
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
});
var lastMsg = '';
var lastMsgTime = '';
$(function() {
    toggleChat();
    update_chat();
});
$(function() {
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
});
$(document).ready(function() {
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
    CSGF.init();
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
    
updateScrollbar();
updateBackground();

var centrifuge = new Centrifuge({
    url: 'ws://beta.mh00.net:8000/connection/websocket',
    user: USER_ID,
    timestamp: CENT_TIME,
    token: CENT_TIKEN
});
centrifuge.connect();
centrifuge.on('connect', function(context) {
    console.log('WebSocket conneted');
});
centrifuge.subscribe("test", function(message) {
    console.log(message);
});
centrifuge.subscribe("update", function(message) {
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
    }, 800);
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
    updateChatScroll();
    addMsg(data);
});
centrifuge.subscribe("chat_del", function(message) {
    var data = message.data;
    updateChatScroll();
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
        updateChatScroll();
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
        updateChatScroll();
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
    });
    centrifuge.subscribe("news", function(message) {
        var data = message.data;
    });
    centrifuge.subscribe("news", function(message) {
        var data = message.data;
    });
}
if(checkUrl('/out')) {
    centrifuge.subscribe("out_new", function(message) {
        var data = message.data;
        $('#last-gout-block').prepend('<img style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="' + data + '" class="scale-in">');
    });
}
var conline = centrifuge.subscribe("online", function(message) {
}).on("join", function(message) {
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
}, function(err) {});