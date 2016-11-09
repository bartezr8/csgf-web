$(function() {
    EZYSKINS.init();
    window.shop = ({
        shop_loader: '<div id="inventory_load" class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div><div style="text-align: center;margin-top: 5px;">Обновляем список предметов!</div>',
        user_money: 0,
        item_tpl: _.template($('#item-template').html()),
        shop_items: {},
        shop_items_Holder: $('#items-list'),
        shop_cart: {},
        shop_cart_price: 0,
        shop_cart_Holder: $('#cards-list'),
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
                price: zipItem.priceCent,
                classid: zipItem.classid,
                shortexterior: zipItem.shortexterior,
                count: zipItem.count,
                filter_exterior: zipItem.exterior,
                filter_rarity: zipItem.rarity,
                filter_rarity_text: zipItem.rarity_text,
                className: zipItem.rarity
            };
            item_obj.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item_obj.classid + '/101fx100f';
            item_obj.el = $(this.item_tpl(item_obj));
            return item_obj;
        },
        draw_items: function(){
            var items_list = makeArray(this.shop_items);
            $('#items-total').text(_.reduce(items_list, function (memo, num) {
                return memo + num.count;
            }, 0));
            $('#filter-total').text(_.reduce(items_list, function (memo, num) {
                return memo + num.count;
            }, 0));
            items_list.sort(function (a, b) {
                return b.price - a.price
            });
            items_list.forEach(function (item) {
                shop.shop_items_Holder.append(item.el);
            });
            this.show_items();
        },
        show_items: function(){
            var args = [];
            ['exterior_all', 'rarity_all', 'type_all'].forEach(function(sel) {
                var exterior = $('#' + sel).val();
                if (exterior) {
                    var arr = [];
                    _.each(exterior, function(tag) {
                        arr.push(window['filter_' + sel][tag]);
                    });
                    args.push(_.union.apply(null, arr));
                }
            });
            var from = parseFloat($('#priceFrom').val()) || 0;
            var to = parseFloat($('#priceTo').val()) || 10e10;
            if (to < from) to = 10e10;
            var items_list = makeArray(shop.shop_items);
            var p = _.filter(items_list, function (item) {
                var _price = Math.round(item.price);
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
            this.update_shop_balance();
            this.clear_items;
            $.ajax({
                url: '/shop/items',
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    if (!data.list.length) return;
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
        update_shop_balance: function(){
            $.post('/getBalance', function (data) {
                $('.userBalance').text(data);
                shop.user_money = data;
            });
        },
        add_new_items: function(data){
            data = JSON.parse(data);
            if (!data.list.length) return;
            data.list.forEach(function (zipItem) {
                var item = shop.parce_item(zipItem);
                if(!is_null(shop.shop_items[item.id])){
                    shop.shop_items[item.id].count += item.count;
                } else {
                    shop.shop_items[item.id] = item;
                }
            });
            this.draw_items();
        },
        buy_item: function(id){
            console.log(shop.user_money);
            if(is_null(shop.shop_items[id])) return $.notify("Предмет не существует", { className: "error" });
            if(shop.shop_items[id].count <= 0) return $.notify("Предмет отсутствует", { className: "error" });
            if((shop.shop_items[id].price + shop.shop_cart_price) > shop.user_money) return $.notify("У вас недостаточно средств", { className: "error" });
            console.log(shop.shop_items[id]);
            if(is_null(shop.shop_cart[id])){
                shop.shop_cart[id] = shop.shop_items[id];
                if(!shop.shiftPress){
                    shop.shop_cart[id].count = 1;
                }
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
            console.log(shop.shop_items[id]);
            shop.shop_cart[id].el = $(this.item_tpl(shop.shop_cart[id]));
            shop.shop_items[id].el = $(this.item_tpl(shop.shop_items[id]));
            this.show_cart();
        },
        show_cart: function(){
            this.shop_cart_Holder.children().replaceWith('');
            var items_list = makeArray(this.shop_cart);
            items_list.sort(function (a, b) {
                return b.price - a.price
            });
            var count = 0;
            var price = 0;
            items_list.forEach(function (item) {
                if(item.count > 0) {
                    shop.shop_cart_Holder.append(item.el);
                    count += 1;
                    price += item.price * item.count;
                }
            });
            this.shop_cart_price = price;
            $('#cart-total').text(count);
            $('#cart-total-price').text(price);
            this.show_items();
        }
    });
    function is_null(data){
        if (data == null) return true;
        return false;
    }
    $(document).on('click', '#items-list .deposit-item', function () {
        shop.buy_item($(this).data('id'));
    });
    function makeArray(object){
        var array = $.map(object, function(value, index) {
            return [value];
        });
        return array;
    }
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
	document.onkeydown = function checkKeycode(event){
		if(!event) var event = window.event;
		var keyShift = event.shiftKey;
		if(keyShift){
			shop.shiftPress = true;
		} else {
			shop.shiftPress = false;
		}
	}
    if(START) {
        ssocket = io.connect(':2085');
        ssocket.on('addShop', function(data) {
            shop.add_new_items(data);
        })
        .on('delShop', function(data) {
            delItems(data);
        })
    }
    function num(val) {
        return Math.round(parseFloat(val)*100)/100;
    }
    shop.load_items();
    setInterval(shop.update_shop_balance,30000);
    
    
    
    /*
function addItems(data) {
    data = JSON.parse(data);
    if (!data.list.length) {
        return;
    }
    addList = [];
    data.list.forEach(function (zipItem) {
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
        if (zipItem.count > 0){
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
                id: zipItem.classid,
                name: zipItem.name,
                steamPrice: zipItem.priceCent,
                sortPrice: zipItem.priceCent,
                priceText: priceText,
                classid: zipItem.classid,
                shortexterior: zipItem.shortexterior,
                count: zipItem.count,
                filter_exterior: zipItem.exterior,
                filter_rarity: zipItem.rarity,
                filter_rarity_text: zipItem.rarity_text,
                className: zipItem.rarity
            };
            if (realListObj[zipItem.classid] === undefined){
                realList.push(obj);
                realListObj[zipItem.classid] = obj;
                realListObj[zipItem.classid].image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + realListObj[zipItem.classid].classid + '/101fx100f';
                realListObj[zipItem.classid].el = $(item_tpl(realListObj[zipItem.classid]));
                itemsHolder.append(realListObj[zipItem.classid].el);
            } else {
                realListObj[zipItem.classid].count = realListObj[zipItem.classid].count + 1;
                realList[realList.indexOf(realListObj[zipItem.classid])].count = realListObj[zipItem.classid].count;
                $('#deposit-item_' + zipItem.classid + ' .deposit-item-wrap .deposit-count').text('x' + realListObj[zipItem.classid].count);
                if (realListObj[zipItem.classid].count > 0){
                    $('#deposit-item_' + zipItem.classid + ':last').removeClass('hidden');
                }
            }
            addList.push(obj);
        }
    });

    $('#items-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));
    $('#filter-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));

    realList.sort(function (a, b) {
        return b.sortPrice - a.sortPrice
    });
    itemsHolder.children().replaceWith('');
    realList.forEach(function (item) {
        item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item.classid + '/101fx100f';
        item.el = $(item_tpl(item));
        itemsHolder.append(item.el);
        if (item.count == 0) item.el.addClass('hidden');
    });
    $('#items-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));
    $('#filter-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));
    allItems = itemsHolder.children('.deposit-item');
	return;
}
function delItems(data) {
    data = JSON.parse(data);
    data.list.forEach(function (classid) {
        if (realListObj[classid] === undefined){
            
        } else {
            if (realListObj[classid].count > 0){
                realListObj[classid].count = realListObj[classid].count - 1;
                realList[realList.indexOf(realListObj[classid])].count = realListObj[classid].count;
            } else {
                if (cardList.indexOf(classid) !== -1) {
                    var j = 0;
                    cardList.splice(cardList.indexOf(classid), 1);
                    for(var i = 0; i < cardList.length; i++) {
                        if (cardList[i] == classid) j++;
                    }
                    $('#cart-total').text(cardList.length);
                    var price = cardPrice - num(realListObj[classid].steamPrice);
                    $('#cart-total-price').text(num(price));
                    cardPrice = num(price);
                    if (j > 0){
                        $('#card-deposit-item_' + classid + ' .deposit-item-wrap .deposit-count').text('x' + j);
                    } else {
                        $('#card-deposit-item_' + classid + ':first').remove();
                        realList.splice(realList.indexOf(realListObj[classid]), 1);
                    }
                }
            }
        }
    });
    $('#items-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));
    $('#filter-total').text(_.reduce(realList, function (memo, num) {
        return memo + num.count;
    }, 0));
    $('#cart-total').text(cardList.length);
    itemsHolder.children().replaceWith('');
    realList.forEach(function (item) {
        item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item.classid + '/101fx100f';
        item.el = $(item_tpl(item));
        itemsHolder.append(item.el);
        if (item.count == 0) item.el.addClass('hidden');
    });
    filterFn;
	return;
}
function buy(id) {
    if(shiftPress){
        var g = realListObj[id].count;
        for (var x = 0; x < g; x++){
            var price = num(cardPrice) + num(realListObj[id].steamPrice);
            if (price <= num($('.userBalance').text())){
                $('#cart-total-price').text(num(price));
                cardPrice = num(price);
                
                realListObj[id].count = realListObj[id].count - 1;
                realList[realList.indexOf(realListObj[id])].count = realListObj[id].count;
                $('#deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + realListObj[id].count);
                if (realListObj[id].count <= 0)$('#deposit-item_' + id + ':last').addClass('hidden');
                cardList.push(realListObj[id].classid);
                $('#cart-total').text(cardList.length);
                if (!$('.deposit-item').is('#card-deposit-item_' + id )){
                    var item = '<div class="deposit-item ' + realListObj[id].className + ' up-' + realListObj[id].className + '" id="card-deposit-item_' + id + '" onclick="sell( ' + id + ' )"><div class="deposit-item-wrap"><div class="img-wrap"><img src="' + realListObj[id].image + '" alt="" title=""/></div><div class="name">' + realListObj[id].name + '</div><div class="deposit-price">' + realListObj[id].priceText + ' <span>руб</span></div><div class="deposit-exterior">' + realListObj[id].shortexterior + '</div><div class="deposit-count">x1</div></div></div>';
                    cartHolder.append(item);
                } else {
                    var j = 0;
                    for(var i = 0; i < cardList.length; i++) {
                        if (cardList[i] == realListObj[id].classid) j++;
                    }
                    $('#card-deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + j);
                }
                filterFn;
            } else {
                $.notify("У вас недостаточно средств", {
                    className: "error"
                });
            }
        }
    } else {
        var price = num(cardPrice) + num(realListObj[id].steamPrice);
        if (price <= num($('.userBalance').text())){
            $('#cart-total-price').text(num(price));
            cardPrice = num(price);
            
            realListObj[id].count = realListObj[id].count - 1;
            realList[realList.indexOf(realListObj[id])].count = realListObj[id].count;
            $('#deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + realListObj[id].count);
            if (realListObj[id].count <= 0)$('#deposit-item_' + id + ':last').addClass('hidden');
            cardList.push(realListObj[id].classid);
            $('#cart-total').text(cardList.length);
            if (!$('.deposit-item').is('#card-deposit-item_' + id )){
                var item = '<div class="deposit-item ' + realListObj[id].className + ' up-' + realListObj[id].className + '" id="card-deposit-item_' + id + '" onclick="sell( ' + id + ' )"><div class="deposit-item-wrap"><div class="img-wrap"><img src="' + realListObj[id].image + '" alt="" title=""/></div><div class="name">' + realListObj[id].name + '</div><div class="deposit-price">' + realListObj[id].priceText + ' <span>руб</span></div><div class="deposit-exterior">' + realListObj[id].shortexterior + '</div><div class="deposit-count">x1</div></div></div>';
                cartHolder.append(item);
            } else {
                var j = 0;
                for(var i = 0; i < cardList.length; i++) {
                    if (cardList[i] == realListObj[id].classid) j++;
                }
                $('#card-deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + j);
            }
            filterFn;
        } else {
            $.notify("У вас недостаточно средств", {
                className: "error"
            });
        }
    }
	return;
}
function sell(id) {
    if(shiftPress){
        var g = 0;
        for(var i = 0; i < cardList.length; i++) {
            if (cardList[i] == realListObj[id].classid) g++;
        }
        for (var x = 0; x < g; x++){
            realListObj[id].count = realListObj[id].count + 1;
            realList[realList.indexOf(realListObj[id])].count = realListObj[id].count;
            $('#deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + realListObj[id].count);
            if (realListObj[id].count > 0){
                $('#deposit-item_' + id + ':last').removeClass('hidden');
            }
            cardList.splice(cardList.indexOf(realListObj[id].classid), 1);
            if (cardList.indexOf(realListObj[id].classid) !== -1) {
                var j = 0;
                for(var i = 0; i < cardList.length; i++) {
                    if (cardList[i] == realListObj[id].classid) j++;
                }
                $('#card-deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + j);
            } else {
                $('#card-deposit-item_' + id + ':first').remove();
            }
            $('#cart-total').text(cardList.length);
            if (cardList.length > 0){
                var price = cardPrice - num(realListObj[id].steamPrice);
                $('#cart-total-price').text(num(price));
                cardPrice = num(price);
            } else {
                $('#cart-total-price').text(0);
                cardPrice = 0;
            }
        }
    } else {
        realListObj[id].count = realListObj[id].count + 1;
        realList[realList.indexOf(realListObj[id])].count = realListObj[id].count;
        $('#deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + realListObj[id].count);
        if (realListObj[id].count > 0){
            $('#deposit-item_' + id + ':last').removeClass('hidden');
        }
        cardList.splice(cardList.indexOf(realListObj[id].classid), 1);
        if (cardList.indexOf(realListObj[id].classid) !== -1) {
            var j = 0;
            for(var i = 0; i < cardList.length; i++) {
                if (cardList[i] == realListObj[id].classid) j++;
            }
            $('#card-deposit-item_' + id + ' .deposit-item-wrap .deposit-count').text('x' + j);
        } else {
            $('#card-deposit-item_' + id + ':first').remove();
        }
        $('#cart-total').text(cardList.length);
        if (cardList.length > 0){
            var price = cardPrice - num(realListObj[id].steamPrice);
            $('#cart-total-price').text(num(price));
            cardPrice = num(price);
        } else {
            $('#cart-total-price').text(0);
            cardPrice = 0;
        }
    }
    filterFn;
}
function getitems() {
    senditems = [];
    for(var i = 0; i < cardList.length; i++) {
        realListObj[cardList[i]].count = realListObj[cardList[i]].count + 1;
        realList[realList.indexOf(realListObj[cardList[i]])].count = realListObj[cardList[i]].count;
    }
    cartHolder.children().replaceWith('');
    $('#cart-total-price').text(0);
    $('#cart-total').text(0);
    senditems = cardList;
    cardList = [];
    cardPrice = 0;
    
    itemsHolder.children().replaceWith('');
    realList.forEach(function (item) {
        item.image = 'https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + item.classid + '/101fx100f';
        item.el = $(item_tpl(item));
        itemsHolder.append(item.el);
        if (item.count <= 0) item.el.addClass('hidden');
    });
    allItems = itemsHolder.children('.deposit-item');
    clearFilter();
    filterFn;
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
            } else {
                if (data.msg) $.notify(data.msg, {className: "error"});
            }
        },
        error: function () {
            $.notify("Произошла ошибка. Попробуйте еще раз", {
                className: "error"
            });
        }
    });
	return;
}
var filterFn = _.debounce(filterAndSort, 200);
function filterAndSort() {
    var args = [];

    ['exterior_all', 'rarity_all', 'type_all'].forEach(function(sel) {
        var exterior = $('#' + sel).val();
        if (exterior) {
            var arr = [];
            _.each(exterior, function(tag) {
                arr.push(window['filter_' + sel][tag]);
            });
            args.push(_.union.apply(null, arr));
        }
    });

    var from = parseFloat($('#priceFrom').val()) || 0;
    var to = parseFloat($('#priceTo').val()) || 10e10;

    if (to < from) to = 10e10;

    var p = _.filter(realList, function (item) {
        var _price = Math.round(item.sortPrice);
        return _price >= from && _price <= to;
    });

    p = _.pluck(p, 'id');
    args.push(p);


    var text = $('#searchInput').val().trim();
    text = text.replace('|', '\\|');
    var p = _.filter(realList, function (item) {
        return (new RegExp(text, 'i').test(item.name));
    });
    p = _.pluck(p, 'id');
    args.push(p);
	allItems = itemsHolder.children('.deposit-item');
    //if (!args.length) return allItems.removeClass('hidden');

    var s = _.intersection.apply(null, args);

    var count = 0;
    allItems.addClass('hidden');
    s.forEach(function (id) {
		if (realListObj[id].count > 0){
			realListObj[id].el.removeClass('hidden');
		}
        count += realListObj[id].count;
    });
    $('#filter-total').text(count);
}
function clearFilter(){
	$('#priceFrom').val('');
	$('#priceTo').val('');
	$('#searchInput').val('');
}*/

});