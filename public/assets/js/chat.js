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
});
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
if(START && checkPage()) {
    csocket = io.connect( SITE_URL , {path:'/csgf-chat', secure: APPS_SECURE, 'force new connection': APPS_FCONNS });
    csocket.on('chat_messages', function(data) {
        updateChatScroll();
        message = data;
        if(message && message.length > 0) {
            $('#messages').html('');
            message = message.reverse();
            for(var i in message) {
                var a = $("#chatScroll")[0];
                var isScrollDown = Math.abs((a.offsetHeight + a.scrollTop) - a.scrollHeight) < 5;
                var CANDEL = false;
                if(IS_MODER == 1) CANDEL = true;
                if(IS_ADMIN == 1) CANDEL = true;
                if(message[i].userid == USER_ID) CANDEL = true;
                var html = '<div class="chatMessage clearfix" data-uuid="' + message[i].id + '" data-user="' + message[i].userid + '" data-username="' + message[i].username + '">';
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
                if($('.chatMessage').length > 255) {
                    $('.chatMessage').eq(0).remove();
                }
            }
            if(isScrollDown) a.scrollTop = a.scrollHeight;
            $("#chatScroll").perfectScrollbar('update');
        }
    })
}
function add_smile(smile) {
    $('#chatInput').val($('#chatInput').val() + smile);
}
$(function() {
    if(checkPage()){
        toggleChat();
        update_chat();
    }
});
function checkPage() {
    /*var ThisPage = window.location.pathname;
    if(ThisPage === "/" || ThisPage === "/double" || ThisPage === "/shop" || ThisPage === "/shop/deposit" || ThisPage === "/coin" || ThisPage === "/dice") return true;
    return false;*/
    return true;
}
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function updateChatMargin() {
    var
        dadCont = $('.dad-container'),
        dadContHeight = dadCont.innerHeight(),
        windowHeight = $(window).innerHeight();
    if(dadContHeight <= windowHeight) {
        $('#chatContainer').css({
            "margin-top": 0
        });
    }
}

function updateChatScroll() {
    var
        chatScroll = $('#chatScroll'),
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
    if(chatPrompt.length) {
        chatHeight = chatHeight - chatPrompt.innerHeight();
    }
    chatScroll.css({
        'height': chatHeight
    });
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
            //update_chat();
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

function toggleChat() {
    //Config variable
    var
        mainContainer = $('.main-container'),
        dadContainer = $('.dad-container'),
        chatBody = $('#chatBody'),
        chatHeader = $('#chatHeader'),
        chatClose = $('#chatClose'),
        chatContainer = $('#chatContainer'),
        chatScroll = $('#chatScroll'),
        viewPortHeight = $(window).innerHeight(),
        viewPortWidth = $(window).innerWidth();
    //Set to chatContainer like viewPortHeight
    chatContainer.css({
        "height": viewPortHeight
    });
    $(window).resize(function() {
        viewPortHeight = $(window).innerHeight();
        chatContainer.css({
            "height": viewPortHeight
        });
    });
    //For test
    $('body').append(chatHeader);
    if(getCookie('chat') !== '0') {
        //Add classes when the page is loaded
        mainContainer
            .addClass('with-chat')
            .find('.dad-container')
            .addClass('with-chat');
        //Show container with chat
        chatContainer.show();
    } else {
        chatHeader.fadeIn();
    }
    //Set viewport height
    setTimeout(updateChatScroll, 0);
    var timerChatCheck = setInterval(updateChatScroll, 1000);
    //Call perfectBar
    chatScroll.perfectScrollbar();
    // chatScroll.scrollTop( chatScroll.prop( "scrollHeight" ) );
    // chatScroll.perfectScrollbar('update');
    //Events
    //Close chat
    chatClose.on('click', function(e) {
        e.preventDefault();
        document.cookie = "chat=0";
        chatContainer.animate({
            width: 'toggle'
        }, 400, function() {
            //Change viewport
            $('meta[name=viewport]').attr('content', 'width=1050');
            mainContainer
                .toggleClass('with-chat')
                .find('.dad-container')
                .toggleClass('with-chat');
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

function replaceLogin(login) {
    function replacer(match, p1, p2, p3, offset, string) {
        var links = ['csgf.ru'];
        return links.indexOf(match.toLowerCase()) == -1 ? '' : match;
    }
    var res = login.replace(/([а-яa-z0-9-]+) *\. *(ru|com|net|gl|su|red|ws|us)+/i, replacer);
    if(!res.trim()) {
        var check = login.toLowerCase().split('csgf.ru').length > 1;
        if(check) {
            res = login;
        } else {
            res = login.replace(/csgo/i, '').replace(/ *\. *ru/i, '').replace(/ *\. *com/i, '');
            if(!res.trim()) {
                res = 'UNKNOWN';
            }
        }
    }
    res = res.split('script').join('srcipt');
    return res;
}

function update_chat() {
    $.ajax({
        type: "GET",
        url: "/chat",
        dataType: "json",
        cache: false,
        success: function(message) {
            if(message && message.length > 0) {
                $('#messages').html('');
                message = message.reverse();
                for(var i in message) {
                    var a = $("#chatScroll")[0];
                    var isScrollDown = Math.abs((a.offsetHeight + a.scrollTop) - a.scrollHeight) < 5;
                    var CANDEL = false;
                    if(IS_MODER == 1) CANDEL = true;
                    if(IS_ADMIN == 1) CANDEL = true;
                    if(message[i].userid == USER_ID) CANDEL = true;
                    var html = '<div class="chatMessage clearfix" data-uuid="' + message[i].id + '" data-user="' + message[i].userid + '" data-username="' + message[i].username + '">';
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
                    if($('.chatMessage').length > 255) {
                        $('.chatMessage').eq(0).remove();
                    }
                }
                if(isScrollDown) a.scrollTop = a.scrollHeight;
                $("#chatScroll").perfectScrollbar('update');
            }
        }
    });
}