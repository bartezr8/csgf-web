$(function() {
    EZYSKINS.init();
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
            this.clear_items;
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
            this.shop_items_Holder.children().replaceWith('');
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
            ['exterior_all', 'rarity_all', 'type_all'].forEach(function(sel) {
                var exterior = $('#' + sel).val();
                if (exterior) {
                    var p = _.filter(items_list, function (item) {
                        var _exterior = item.filter_exterior;
                        return _exterior == exterior;
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
                    console.log(data);
                    if (!data.list.length) return;
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
            item.filter_exterior = zipItem.filter_exterior;
            item.filter_rarity = zipItem.filter_rarity;
            item.filter_rarity_text = zipItem.filter_rarity_text;
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
            if(shop.shop_cart[id].count <= 0) return $.notify("Предмет отсутствует", { className: "error" });
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
                if(item.count > 0) {
                    shop.sell_cart(item.id);
                }
            });
        },
        get_cart: function(){
            var senditems = [];
            var items_list = makeArray(this.shop_cart);
            items_list.forEach(function (item) {
                if(item.count > 0) {
                    for (var i = 0; i < item.count; i++) {
                        senditems.push(item.id);
                    }
                }
            });
            console.log(senditems);
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
    $(document).on('change', '#exterior_all', '#select_page', shop.show_items);
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
    function num(val) {
        return Math.round(parseFloat(val)*100)/100;
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
});