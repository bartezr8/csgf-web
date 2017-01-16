<div class="deposit-item {{ $sale['className'] }} up-{{ $sale['className'] }}" id="shop-item_{{ $sale['id'] }}" onclick="buySale( {{ $sale['id'] }} )" data-id="{{ $sale['id'] }}" data-original-title="{{ $sale['name'] }}" style="margin: 0;width: 86px;border-right: 1px solid #2f5463;">
    <div class="deposit-item-wrap">
        <div class="img-wrap"><img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/{{ $sale['classid'] }}/101fx100f" style="margin-top: 0px;" alt="" title=""></div>
        <div class="deposit-price" style="bottom: 65px;color: #7d7d7d;"><s>{{ $sale['oldprice'] }}</s> <span><s>руб</s></span></div>
        <div class="deposit-price" style="color: #51ff4f;">{{ $sale['price'] }} <span>руб</span></div>
    </div>
</div>