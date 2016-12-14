$(function() {
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
                    $('.userBalance').text(num(num($('.userBalance').text()) - num(sum)));
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
    function num(val) {
        return Math.round(parseFloat(val)*100)/100;
    }

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
    socket.on('dice', function(data) {
        data = JSON.parse(data);
        setTimeout(function(){
            dice.addLastGame(data);
        }, 1500);
    });
});
function num(val) {
    return Math.round(parseFloat(val)*100)/100;
}