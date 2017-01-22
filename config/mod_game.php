<?php
return [
    'gifts' => false,
    'appid' => 730,                 // APPID игры
    'max_items' => 50,              // Предметов за игру
    'min_price' => 0.1,             // Минимальная ставка
    'game_items' => 200,            // Всего предметов за игру
    'game_min_price' => 1,          // Игра от суммы
    'game_low_chanse' => 6,         // Шансы на лоу (чем больше цифра - меньше шансы)
    'players_to_start' => 2,        // Игра от кол-ва игроков
    'max_items_per_trade' => 50,    // Предметов за трейд
    
    'chat_history_length' => 50,    // Длина чата.    
    'slimit' => 5,                  // Процент начислений на вывод
    'slimit_default' => 5,          // Изначальный лимит

    'comission' => 7,              // Комиссия на сайте
    'comission_first_bet' => 1,     // - ком. за первую ставку
    'comission_site_nick' => 2,     // - ком. за сайт в нике
    'comission_minchance' => 98,    // процент от которого ком. только с выигрыша
    'comission_to_shop' => true,    // Отправка комиссии в магазин
    
    'bot_steamid' => 76561198067721846,     // Не актуально пока не перепишу магазин
    'shop_steamid64' => 76561198073063637,  // Кому отправлять
    
    'bonus_bot' => true,                            // Бонус бот
    'bonus_bot_steamid64' => 76561197960265728,     // SteamID BB
    
    'backpack_key' => '5611565eb98d880409cc1ac0',   // Ключ для сайта backpack.tf
    'urls' => ['рулетка кс го для бомжей','csgf','рулетка для бомжей','рулетка кс го','кс го рулетка для бомжей','рулетки кс го для бомжей','рулетки для бомжей','рулетки кс го','рулетка кс го от 1 рубля','рулетки кс го от 1 рубля'],    // Слова для блока помогите нам.
    'bots' => [
        '0' => 'https://steamcommunity.com/tradeoffer/new/?partner=107456118&token=rM1bEl8B',
        '1' => 'https://steamcommunity.com/tradeoffer/new/?partner=291354444&token=yN0pufjQ',
        '2' => 'https://steamcommunity.com/tradeoffer/new/?partner=250629935&token=BN3dagO_'
    ]
];
