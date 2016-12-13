if(START) {
    socket.on('coin_new', function(data) {
        data = JSON.parse(data);
        var audio = new Audio('/assets/sounds/coin/coinnew.ogg');
        if(sound_status) audio.play();
        $('#cointable').append($(coin_tpl(data)));
    }).on('coin_scroll', function(data) {
        data = JSON.parse(data);
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
$(function () {
    window.coin_tpl = _.template($('#coin-template').html());
});
function num(val) {
    return Math.round(parseFloat(val)*100)/100;
}
$(document).ready(function () {});

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