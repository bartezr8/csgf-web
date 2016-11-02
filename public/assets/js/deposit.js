$(document).ready(function () {
    $(document).on('click', '#shopDepBut', function () {
        $('#shopDepositModal').arcticmodal();
    });
});
$(function () {
    EZYSKINS.init();

	window.dcardPrice = 0;
	window.shiftPress = false;
	window.depitems = [];
	window.depitemsobj = {};
	window.depitemscart = [];
	document.onkeyup = function checkKeycode(event){
		if(!event) var event = window.event;
		var keyShift = event.shiftKey;
		if(keyShift){
			window.shiftPress = true;
		} else {
			window.shiftPress = false;
		}
	}
	document.onkeydown = function checkKeycode(event){
		if(!event) var event = window.event;
		var keyShift = event.shiftKey;
		if(keyShift){
			window.shiftPress = true;
		} else {
			window.shiftPress = false;
		}
	}

	window.ditemsHolder = $('#dep-items-list');
	window.dcartHolder = $('#dep-cart-list');
    window.allItems;
    window.item_tpl = _.template($('#item-template').html());
	
	update_dep_items();
	
    $(document.body).on('click', '.myhistorylink', function (e) {
		setTimeout(updateBalance,1000);
    });

});

function update_dep_items() {
	depitems = [];
	depitemsobj = {};
	depitemscart = [];
	ditemsHolder.children().replaceWith('');
	ditemsHolder.html('<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>');
	$('#dcart-total').text(0);
	$('#dcart-total-price').text(0);
	dcartHolder.children().replaceWith('');
	$.ajax({
		url: '/shop/myinventory',
		type: 'POST',
		dataType: 'json',
		success: function (data) {
            console.log(data);
			if (!data.list.length || !data.success) {
				ditemsHolder.children().replaceWith('');
				ditemsHolder.html('<div style="text-align: center">Ваш инвентарь пуст!</div>');
				return;
			}
			data.list.forEach(function (zipItem) {
				var i = 0;
				zipItem = {
					id: zipItem[i++],
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
				}
				if (zipItem.exterior == 'Немного поношенное') {
					zipItem.shortexterior = 'MW';
				}
				if (zipItem.exterior == 'После полевых испытаний') {
					zipItem.shortexterior = 'FT';
				}
				if (zipItem.exterior == 'Поношенное') {
					zipItem.shortexterior = 'WW';
				}
				if (zipItem.exterior == 'Закаленное в боях') {
					zipItem.shortexterior = 'BS';
				}
				if (zipItem.exterior == null) {
					zipItem.shortexterior = '*';
				}
				var priceText = zipItem.priceCent;

				if(zipItem.name.length > 35) {
					zipItem.name = zipItem.name.substr(0, 34) + '...'
				}

				if(zipItem.exterior !== null) {
					if (zipItem.exterior.length > 19) {
						zipItem.exterior = zipItem.exterior.substr(0, 16) + '...'
					}
				}

				var obj = {
					id: zipItem.id,
					name: zipItem.name,
					steamPrice: zipItem.priceCent,
					sortPrice: zipItem.priceCent,
					priceText: priceText,
					count: 1,
					classid: zipItem.classid,
					shortexterior: zipItem.shortexterior,
					filter_exterior: zipItem.exterior,
					filter_rarity: zipItem.rarity,
					filter_rarity_text: zipItem.rarity_text,
					className: zipItem.rarity
				};
				depitems.push(obj);
				depitemsobj[zipItem.id] = obj;
		
			});

			depitems.sort(function (a, b) {
				return b.sortPrice - a.sortPrice
			});
			ditemsHolder.children().replaceWith('');
			depitems.forEach(function (item) {
				item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item.classid + '/101fx100f';
				item.el = $(item_tpl(item));
				ditemsHolder.append(item.el);
			});
		},
		error: function () {
			ditemsHolder.children().replaceWith('');
			ditemsHolder.html('<div style="text-align: center">Ваш инвентарь пуст!</div>');
		}
	});
}
function num(val) {
	return Math.round(parseFloat(val)*100)/100;
}
function buy(id) {
    if(depitemscart.length <= 50){
        dcartHolder.append('<div class="deposit-item ' + depitemsobj[id].className + ' up-' + depitemsobj[id].className + '" id="dcard-deposit-item_' + id + '" onclick="sell( ' + id + ' )"><div class="deposit-item-wrap">' + $('#dep-items-list #deposit-item_' + id + ':first').html() + '</div>');
        depitemscart.push(id);
        $('#dep-items-list #deposit-item_' + id + ':first').remove();
        var price = num(dcardPrice) + num(depitemsobj[id].steamPrice);
        $('#dcart-total-price').text(num(price));
        $('#dcart-total').text(depitemscart.length);
        dcardPrice = num(price);
    } else {
        $.notify("Максимум 50 предметов", {
            className: "error"
        });
    }
	return;
}
function inv_update(){
	ditemsHolder.html('<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>');
	$.ajax({
		url: '/shop/inv_update',
		type: 'POST',
		dataType: 'json',
		success: function (data) {
			$.notify('Инвентарь обновлен', {className: "success"});
			update_dep_items();
		},
		error: function () {
			$.notify("Произошла ошибка. Попробуйте еще раз", {
				className: "error"
			});
		}
	});
}
function checkOffers(){
	ditemsHolder.html('<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>');
	$.ajax({
		url: '/shop/checkOffers',
		type: 'POST',
		dataType: 'json',
		success: function (data) {
			
			if (data.success) {
				if(data.active){
					$("#acceptTradeUrl").show();
					$("#acceptTradeUrl").attr("href", "https://steamcommunity.com/tradeoffer/" + data.tradeid);
				} else {
					$("#acceptTradeUrl").hide();
				}
				$.notify(data.msg, {className: "success"});
			} else {
				if (data.msg) $.notify(data.msg, {className: "error"});
			}
			update_dep_items();
			updateBalance();
		},
		error: function () {
			$.notify("Произошла ошибка. Попробуйте еще раз", {
				className: "error"
			});
		}
	});
}
function sell(id) {
    ditemsHolder.append(depitemsobj[id].el);
    depitemscart.splice(depitemscart.indexOf(id), 1);
    $('#dcard-deposit-item_' + id + ':first').remove();
    $('#dcart-total').text(depitemscart.length);
    if (depitemscart.length > 0){
        var price = dcardPrice - num(depitemsobj[id].steamPrice);
        $('#dcart-total-price').text(num(price));
        dcardPrice = num(price);
    } else {
        $('#dcart-total-price').text(0);
        dcardPrice = 0;
    }
	return;
}
function getitems() {
    ditemsHolder.html('<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>');
    senditems = [];
    depitemscart.forEach(function (id) {
        senditems.push(depitemsobj[id].id);
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
                $.notify(data.msg, {className: "success"});
            } else {
                if (data.msg) $.notify(data.msg, {className: "error"});
            }
            $("#acceptTradeUrl").show();
            $("#acceptTradeUrl").attr("href", "https://steamcommunity.com/tradeoffer/" + data.tradeid);
            update_dep_items();
            updateBalance();
        },
        error: function () {
            $.notify("Произошла ошибка. Попробуйте еще раз", {
                className: "error"
            });
        }
    });
	return false;
}