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
                        <div class="add-balance-block" style="padding-top: 20px;padding:0;text-align: left;padding-left: 40px;">
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
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 40px;">
                            <div class="balance-item">
                                Инвентарем CSGO:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 64px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/shop/deposit" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" btn-vk ">Депозит</a>
                            </div>
                        </div>
                        <div class="add-balance-block" style="padding:0;text-align: left;padding-left: 40px;">
                            <div class="balance-item">
                                Реферальная система:
                            </div>
                            <div style="text-align: right;display: inline-block;margin-left: 36px;">
                                <span class="icon-arrow-right"></span>
                                <a href="/ref" target="_blank" style="width: 270px;display: inline-block;vertical-align: middle;float: none;padding: 0px 25px;font-size: 12px;font-weight: 400;height: 30px;line-height: 30px;" class=" add-deposit ">Пригласить</a>
                            </div>
                        </div>
                        <div class="box-modal-footer">
                            <div class="msg-wrap" style="position: relative;">
                                <div class="close-this-msg box-modal_close" style="top: 6px; right: 6px; opacity: 0.8;"></div>
                                <div class="msg-green" style="margin-left: 12px;margin-top: 20px;">
                                    <h2>Получи +{{ config('pay.factor') }}% от суммы при пополнении баланса</h2>
                                    <p>Вы можете получить <span>+{{ config('pay.factor') }}%</span> от пополнения, независимо от самой суммы.</p>
                                    <p>Для этого вам необходимо добавить в ник  <span><b>{{ config('app.sitename') }}</b></span> и перезайти на стайт.</p>
                                    <p>После оплаты вам зачислится увеличенная сумма платежа.</p>
                                </div>
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
				<div class="title-block"><h2>Лимиты на вывод</h2></div>
			</div>
			<div class="text-block-wrap">
				<div class="text-block">
					<p class="lead-big">На нашем сайте присутствует накопительная система вывода из магазина.</p>                    
					<p class="lead-big" style="margin: 0px -20px 15px;background: rgba(20, 34, 41, 0.5);padding: 15px;-webkit-box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);box-shadow: inset 0px 0px 10px -2px rgba(12, 19, 23, 0.5);color: rgb(179, 218, 179);">
                        С каждой ставки вы получаете <span>+{{ config('mod_game.slimit') }}%</span> от суммы к лимиту.<br>
                        Поставив <span>1000р.</span> вы увеличите лимит на <span>{{ 10 * config('mod_game.slimit') }}р</span>.
                    </p>
					<p class="lead-normal">Лимит увеличивается на полную сумму <span>пополнения</span> или <span>депозита</span>.</p>
					<p class="lead-normal">Увеличение лимита на <span>{{ config('mod_game.slimit') }}%</span> начисляется с любых игр на рулетке.</p>
					<p class="lead-normal">Изначально у вас есть <span>бонусные {{ config('mod_game.slimit_default') }}р</span> на вывод для обналичивания реферала.</p>
					<p class="lead-normal">Ограничение действет на <span>ВСЕ</span> предметы в магазине.</p>
				</div>
			</div>
		</div>
	</div>
    
    <!--div class="box-modal affiliate-program" id="vk-post">
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
	</div-->
</div>