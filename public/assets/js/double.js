if(START) {
    dsocket = io.connect( SITE_URL , {path:'/csgf-double', secure: APPS_SECURE, 'force new connection': APPS_FCONNS });
    var ngtimerStatus = true,
        sld = false;
    dsocket.on('ngdouble', function(data) {
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
        sld = false;
    })
    .on('dbtimer', function(time) {
        if(!sld) {
            $('#barContainer').slideDown();
            sld = true;
        }
        $('.item-bar').css('width', ((35 - time) / 35) * 100 + '%');
        var audio_scroll = new Audio('/assets/sounds/timer.mp3');
        var audio_notice = new Audio('/assets/sounds/notice.mp3');
        $('#gameTimer .countMinutes').text(lpad(Math.floor(time / 60), 2));
        $('#gameTimer .countSeconds').text(lpad(time - Math.floor(time / 60) * 60, 2));
        if(time < 35 && sound_status) audio_scroll.play();
        if(time == 35 && sound_status) audio_notice.play();
    })
    .on('doubleslider', function(data) {
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
    })
    .on('nbdouble', function(data) {
        data = JSON.parse(data);
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
    })
}
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