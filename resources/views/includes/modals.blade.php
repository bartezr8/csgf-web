<div style="display: none;">
    @if(!Auth::guest())
    <div class="box-modal b-modal-cards" id="msend">
        <div class="box-modal-container">
            <div class="box-modal_close arcticmodal-close"></div>
            <div class="box-modal-content">
                <div class="add-balance-block">
                    <div class="balance-item">
                        Ваш баланс:
                        <span class="userBalance">{{ $u->money }} </span> <div class="price-currency">рублей</div>
                    </div>

                    <span class="icon-arrow-right"></span>
                    <div class="input-group">
                        <input type="text" id="mssum" pattern="^[ 0-9.]+$" maxlength="5" placeholder="Введите сумму">
                        <button type="submit" class="btn-add-balance" id="msb">перевести</button>
                    </div>
                </div>
                <div class="cards-cont">
                    <div class="msg-wrap" style="margin-bottom: -17px;">
                        <div class="icon-warning"></div>
                        <div class="msg-green msg-mini" id="whenLoadingOrNoCardsOrTitle">Средства придут: <a id="smid" href="/user/76561198073063637" target="_blank">76561198073063637</a></div>
                    </div>
                </div>
                <div class="user-winner-block" id="smb" style="display: block;">
                    <div class="user-winner-table" style="padding-bottom: 0px;margin: 0px;">
                        <table>
                            <thead>
                                <tr>
                                    <td style="text-align: center; padding-left: 0px;" class="winner-name-h">От</td>
                                    <td style="text-align: center; padding-left: 0px;" class="round-sum-h">Для</td>
                                    <td style="text-align: center; padding-left: 0px;" class="winner-name-h">Сумма</td>
                                </tr>
                            </thead>
                            <tbody id="smlast">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-modal affiliate-program" id="addBalMod">
        <div class="box-modal-head">
            <div class="box-modal_close arcticmodal-close"></div>
        </div>
        <div class="box-modal-content">
            <div class="content-block">
                <div class="title-block">
                    <h2>Пополнение</h2>
                </div>
            </div>
            <div class="b-modal-cards" style="border: none; width: 609px; border-radius: 0px;" id="cardDepositModal">
                <div class="box-modal-container">
                    <div class="box-modal-content">
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Через платежные системы:
                            </div>
                            <span class="icon-arrow-right"></span>
                            <div id="GDonate" class="input-group">
                                <form method="GET" style="margin-bottom: 0;" action="/pay">
                                    <input type="text" name="sum" placeholder="Введите сумму">
                                    <button type="submit" class="btn-add-balance" name="">пополнить</button>
                                </form>
                            </div>
                        </div>
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Инвентарем CSGO:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 64px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/shop/deposit" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" btn-vk ">Депозит</a>
                            </div>
                        </div>
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 25px;">
                            <div class="balance-item">
                                Реферальная система:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 36px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/ref" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" add-deposit ">Пригласить</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
	<div class="box-modal affiliate-program" id="level-popup">
		<div class="box-modal-head">
			<div class="box-modal_close arcticmodal-close"></div>
		</div>
		<div class="box-modal-content">
			<div class="content-block">
				<div class="title-block"><h2>Уровень игрока</h2></div>
			</div>
			<div class="text-block-wrap">
				<div class="text-block">
					<p class="lead-big">Чем выше ваш уровень – тем больше вы можете выводить из магазина.</p>                    
					<p class="lead-big" style="margin: 0px -20px 15px;background: rgba(20, 34, 41, 0.5);padding: 15px;-webkit-box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);color: rgb(179, 218, 179);">За каждую поставленную <span>1000р</span> вы получаете <span>+1 уровень</span>.<br><span>+1 уровень</span> = <span>+{{ config('mod_shop.max_daily_sum') }}р</span> вывода из магазина в сутки.</p>
					<p class="lead-normal">Уровень повышается <span>от ваших ставок</span> на сайте.</p>
					<p class="lead-normal">Таким образом вам следует <span>больше ставить</span> если вы хотите выводить большие суммы из магазина. Максимальный уровень - <span>50</span>. Запрещена накрутка уровня!</p>
					<p class="lead-normal">Сумма внесенная в магазин за сутки <span>увеличивает сумму вывода</span> соответственно.</p>
					<p class="lead-normal">Сумма зачисленная на баланс за сутки <span>увеличивает сумму вывода</span> аналогично.</p>
					<p class="lead-normal">Вы можете выводить <span>{{ config('mod_shop.max_daily_sum') }}</span> * <span>уровень</span> в сутки + <span>2 суммы(↑↑↑)</span>.</p>
					<p class="lead-normal">Ограничение действет на предметы от <span>5р</span>.</p>
				</div>
			</div>
		</div>
	</div>	
    <div class="box-modal affiliate-program" id="vk-post">
		<div class="box-modal-head">
			<div class="box-modal_close arcticmodal-close"></div>
		</div>
		<div class="box-modal-content">
			<div class="content-block">
				<div class="title-block"><h2>Внимание!</h2></div>
			</div>
			<div class="text-block-wrap">
                <div id="vk_post_-35255262_2593"></div>
                <script type="text/javascript">
                  (function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//vk.com/js/api/openapi.js?136"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'vk_openapi_js'));
                  (function() {
                    if (!window.VK || !VK.Widgets || !VK.Widgets.Post || !VK.Widgets.Post('vk_post_-35255262_2593', -35255262, 2593, 'fceMrYJ7o-iW2ulzPw9MhX5lmaY', {width: 608})) setTimeout(arguments.callee, 50);
                  }());
                </script>
			</div>
		</div>
	</div>
</div>