var onpage = true;
var averagebettime = 5.5;
function updateBackground() {
    var mainHeight = $('.dad-container').height();
    var windowHeight = $(window).height();
    if(mainHeight > windowHeight) {
        $('.main-container').height('auto');
    } else {
        $('.main-container').height('auto');
    }
}
$(function() {
    if($.cookie('averagebettime') !== null) {
        averagebettime = $.cookie('averagebettime');
        $('#speed_trades').html(averagebettime);
    } else {
        $('#speed_trades').html(averagebettime);
    }
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
    $(window).blur(function() {
        onpage = false;
    });
    $(window).focus(function() {
        onpage = true;
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
        $('#vk-post').arcticmodal();
        $.cookie('vk_post', 'true', {
            expires: 0.1,
            path: '/',
        });
    }
    
    $('a[href="' + document.location.pathname + '"]').parent().addClass('active');
    $('.deposit-item:not(.card)').tooltip({
        container: 'body'
    });
    $('[data-toggle="popover"]').popover({
        "container": "body"
    });
    EZYSKINS.init();
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
                    steamid: sendmoneyid,
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
var sendmoneyid = 76561198073063637;

function sendMoney(userid) {
    sendUpdate();
    sendmoneyid = userid;
    $('#smid').attr("href", "/user/" + sendmoneyid);
    $('#smid').text(sendmoneyid);
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

function my_comm() {
    $.ajax({
        url: '/my_comission',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#my_comission').html(data);
        }
    });
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
updateScrollbar();
updateBackground();
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

function lpad(str, length) {
    while(str.toString().length < length)
        str = '0' + str;
    return str;
}
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
    block
        .stop(true, false)
        .animate({
            scrollLeft: direction + "=250"
        });
});
setInterval(function(){ConntectSocketIO();},10);
function updateUsers(){
    r = Math.floor(Math.sqrt(57660 / $('#win-block').children().length)) - 1;
    $(".onine_user").css({
        'height': r,
        'width': r
    });
}
function ConntectSocketIO(){
    if( !CONNECT ) {
        socket = io.connect( SITE_URL , {path:'/csgf-app', secure: APPS_SECURE, 'force new connection': APPS_FCONNS });
    }
}
function updateSocketIO(){
    if( !CONNECT ) {
        socket.emit('steamid64', USER_ID);
        CONNECT = true;
    }
}
if(START && onpage) {
    var declineTimeout,
        r = 0,
        timerStatus = true,
        ngtimerStatus = true,
        onlineList = [];
    updateSocketIO();
    socket.on('update', function(data) {
        if(data){
            updateSocketIO();
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
        }
    })
    .on('status', function(data) {
        updateSocketIO();
        $('#statBot').removeAttr('title');
        $('#statBot').removeAttr('data-original-title');
        $('#statBot').attr('title', data.rus);
        $('#statBot').attr('data-original-title', data.rus);
        $('#statBot').toggleClass(data.stat);
    })
    .on('notification', function(data) {
        updateSocketIO();
        if(data) {
            response = JSON.parse(data);
            var n = data.indexOf(USER_ID);
            if(n !== -1) {
                if(USER_ID != 76561197960265728) {
                    updateBalance();
                    $.notify(response.message, {
                        clickToHide: 'true',
                        autoHide: "false",
                        className: "success"
                    });
                }
            }
        }
    })
    if(checkUrl('/')) {
        /*bsocket = io.connect( SITE_URL , {path:'/csgf-bot', secure: APPS_SECURE, 'force new connection': APPS_FCONNS });
        bsocket.on('queue', function(data) {
            updateSocketIO();
            if(data) {
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
            }
        })
        .on('bettime', function(data) {
                updateSocketIO();
                data = JSON.parse(data);
                averagebettime = Math.round(((averagebettime * 100) + data) / 200) / 10;
                $('#speed_trades').html(averagebettime);
                $.cookie('averagebettime', averagebettime, {
                    expires: 5,
                    path: '/',
                });
        });*/
        updateSocketIO();
        socket.on('newDeposit', function(data) {
            updateChatScroll();
            //if(USER_ID != 76561197960265728) updateBalance();
            data = JSON.parse(data);
            if(data) {
                if(data.betprice < 10) {
                    var audio = new Audio('/assets/sounds/betLow.mp3');
                } else if(data.betprice < 50) {
                    var audio = new Audio('/assets/sounds/betMedium.mp3');
                } else {
                    var audio = new Audio('/assets/sounds/betHigh.mp3');
                }
                if(sound_status) audio.play();
            }
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
            updateBackground();
            $('#roundBank').html(Math.round(data.gamePrice) + ' <span class="money" style="color: #b3e5ff;">руб</span>');
            $('title').text(Math.round(data.gamePrice) + ' р. | CSGF.RU');
            $('.item-bar-text').html('<span>' + data.itemsCount + '<span style="font-weight: 100;"> / </span>100</span>' + n2w(data.itemsCount, [' предмет', ' предмета', ' предметов']));
            $('.item-bar').css('width', data.itemsCount + '%');
            $('.deposit-item').tooltip({
                container: 'body',
                placement: 'top'
            });
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
            $('.current-user').tooltip({
                container: 'body'
            });
            my_comm();
            EZYSKINS.initTheme();
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
        })
        .on('forceClose', function() {
            updateSocketIO();
            $('.forceClose').removeClass('msgs-not-visible');
        })
        .on('online_add', function(data) {
            updateSocketIO();
            if(!$("a").is("#online_id_" + data.steamid64)) {
                $('#win-block').append("<a id='online_id_" + data.steamid64 + "' href='/user/" + data.steamid64 + "' target='_blank'><img title='" + data.username + "' class='onine_user' src=" + data.avatar + "></img></a>");
                //onlineList.push(data.steamid64);
                $("#online_id_" + data.steamid64).addClass("scale-in");
                $("#online_id_" + data.steamid64).show();
            }
            setTimeout(function() {updateUsers();}, 1000);
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
        })
        .on('online_del', function(data) {
            updateSocketIO();
            if($("a").is("#online_id_" + data.steamid64)) {
                $("#online_id_" + data.steamid64).toggleClass("scale-in scale-out")
                setTimeout(function() {
                    $("#online_id_" + data.steamid64).remove();
                    setTimeout(function() {updateUsers();}, 1000);
                }, 1000);
            }
        })
        .on('timer', function(time) {
            updateSocketIO();
            function getRandomInt(min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            var audio_scroll = new Audio('/assets/sounds/scr.mp3');
            var audio_notice = new Audio('/assets/sounds/notice.mp3');
            var audio_last5s = new Audio('/assets/sounds/timer-tick-last-5-seconds.mp3');
            $('#gameTimer .countMinutes').text(lpad(Math.floor(time / 60), 2));
            $('#gameTimer .countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
            /*if(time < 30 && time > 5 && sound_status) audio_scroll.play();
            if(time <= 5 && sound_status) audio_last5s.play();*/
            if(time == 30 && sound_status) audio_notice.play();
        })
        .on('slider', function(data) {
            updateSocketIO();
            if(data.userchanses != null) {
                if(data.time != null) $('#newGameTimer .countSeconds').text(lpad(data.time, 2));
                if(ngtimerStatus) {
                    ngtimerStatus = false;
                    $('#roundFinishBlock').hide();

                    function getRandomInt(min, max) {
                        return Math.floor(Math.random() * (max - min + 1)) + min;
                    }
                    var users = data.userchanses;
                    var a_this_scrol = getRandomInt(112, 165)
                    users = mulAndShuffle(users, Math.ceil(180 / users.length));
                    users[a_this_scrol] = data.winner;
                    html = '';
                    var j = 0;
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
                    var easetype = 'easeOutCirc';
                    $('#usersCarousel').css('margin-left', -41);
                    if(data.showSlider) {
                        var audio = new Audio('/assets/sounds/rollscrollskoniks.mp3');
                        if(sound_status) audio.play();
                        $('#usersCarousel').animate({
                            marginLeft: scrollmar
                        }, 1000 * (data.time - 10), easetype, function() {
                            $('#li_winner_112').css({
                                'height': '70px'
                            });
                            $('#div_winner_112').slideDown();
                            $('#li_winner_112').addClass("rotate");
                            $('#usersCarouselConatiner').css({
                                'z-index': '107'
                            });
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
                                setTimeout(function() {
                                    $('#toTrade').fadeOut();
                                }, 30000);
                            }
                            $('#usersCarousel').animate({
                                marginLeft: -this_scrol
                            }, 2000, easetype, function() {});
                        });
                    } else {
                        $('#usersCarousel').animate({
                            marginLeft: scrollmar
                        }, 1000, easetype, function() {
                            $('#li_winner_112').css({
                                'height': '70px'
                            });
                            $('#div_winner_112').slideDown();
                            $('#li_winner_112').addClass("rotate");
                            $('#usersCarouselConatiner').css({
                                'z-index': '107'
                            });
                            $('#winnerInfo .winner-info-holder').slideDown();
                            $('#roundFinishBlock').show();
                            $('#usersCarousel').animate({
                                marginLeft: -this_scrol
                            }, 1000, easetype, function() {});
                        });
                    }
                }
            }
        })
        .on('newGame', function(data) {
            updateSocketIO();
            if(USER_ID != 76561197960265728) {
                if(USER_ID != 76561197960265728) {
                    updateBalance();
                }
            }
            $('html, body').animate({
                'scrollTop': '0'
            }, 'slow');
            updateChatScroll();
            $('#usersCarouselConatiner').css({
                'z-index': '0'
            });
            var audio = new Audio('/assets/sounds/newgamestartm.mp3');
            if(sound_status) audio.play();
            $('#usersChances .users .current-chance-wrap').html('');
            $('#usersChances .items .current-chance-wrap').html('');
            $('#usersChances').hide();
            /*$('#div_winner_112').hide();*/
            $('#deposits').html('');
            updateBackground();
            $('#myItemsCount').html('0 <span style="font-size: 12px;"> предметов</span>');
            $('#myChance').text('0%');
            $('#roundId').text(data.id);
            $('#roundBank').html('0 <span class="money" style="color: #b3e5ff;">руб</span>');
            $('#hash').text(data.hash);
            $('.item-bar-text').html('<span>0</span> предметов');
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
        })
        .on('depositDecline', function(data) {
            updateSocketIO();
            data = JSON.parse(data);
            if(data.user == USER_ID) {
                clearTimeout(declineTimeout);
                declineTimeout = setTimeout(function() {
                    $('#errorBlock').slideUp();
                }, 1000 * 10)
                $('#errorBlock p').text(data.msg);
                $('#errorBlock').slideDown();
            }
        })
    }
    if(checkUrl('/out')) {
        socket.on('out_new', function(data) {
            updateSocketIO();
            $('#last-gout-block').prepend('<img style="border: 1px solid rgb(47, 84, 99); height: 42px; width: 42px; margin: 5px;" src="' + data + '" class="scale-in">');
        })
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
    if(pathname == url) {
        return true;
    } else {
        return false;
    }
}

function formatDate(date) {
    moment(date).format('DD/MM/YYYY - <span>h:mm</span>');
}
$.notify.addStyle('custom', {
    html: "<div>\n<span data-notify-text></span>\n</div>"
});
$.notify.defaults({
    style: "custom"
});
$(document).on('mouseenter', '.iusers, .iskins', function() {
    $(this).tooltip('show');
});

function load_page() {
    if(!LOAD) {
        LOAD = true;
        var $preloader = $('#page-preloader'),
            $spinner = $preloader.find('.spinner');
        $spinner.fadeOut();
        if(USER_ID != 76561197960265728) {
            my_comm();
        }
        $preloader.delay(350).fadeOut('slow');
    }
}
/*$(window).on('load', function() {
    setInterval(function(){
        if( !CONNECT ) socket = io.connect(':2082');
    }, 5000);
    //setTimeout(function() {
        //load_page();
    //}, 10);
    //}, 2000);
});*/