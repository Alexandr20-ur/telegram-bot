<?php
/*
* @name        DARKLYY
* @link        https://darklyy.ru/
* @copyright   Copyright (C) 2012-2023 ООО «ПРИС»
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      Alexander (04.10.2023 11:08)
*/

namespace App\Http\Controllers;


use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class TelegramController extends Controller
{

    /**
     * Вывод сообщения по команде /start
     *
     * @param TelegraphChat $chat
     * @param string $firstName
     * @return void
     */
    static function mainText(TelegraphChat $chat, string $firstName): void
    {
        $message = "Здравствуйте *". $firstName . "!* Прошу ознакомиться с возможностями данного бота!";
        $chat->markdown($message)->keyboard(Keyboard::make()
            ->row([
                Button::make('🛒 Каталог')->action('catalog'),
                Button::make('🔎 Поиск')->action('search'),
                Button::make('❕ Информация')->action('read')->param('id', $chat->chat_id),
                Button::make('📢 Поддержка')->url('https://t.me/epccep'),
            ])
            ->chunk(2)
            ->row([
                Button::make('🛍️ Корзина')->action('read')->param('id', $chat->chat_id),
            ])
            ->row([
                Button::make('📦 Мои заказы')->action('read')->param('id', $chat->chat_id),
            ]))->send();
    }
}
