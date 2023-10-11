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


use App\Models\Good;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Support\Collection;

class TelegramController extends Controller
{

    /**
     * Вывод сообщения по команде /start
     *
     * @param TelegraphChat $chat
     * @param string $firstName
     * @return Telegraph
     */
    static function mainText(TelegraphChat $chat, string $firstName): Telegraph
    {
        $message = "Здравствуйте *". $firstName . "!* \nПрошу ознакомиться с возможностями данного бота!";
        return $chat->markdown($message)->keyboard(Keyboard::make()
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
            ]));
    }

    /**
     * Карточка товара
     * @param TelegraphChat $chat
     * @param $good
     * @param int $offset
     * @return Telegraph
     */
    static function cardText(TelegraphChat $chat, $good, int $offset = 0): Telegraph
    {
        $buttonRow = [
            Button::make('⬅️')->action('goods')->param('offset', $offset - 1),
            Button::make('➡️')->action('goods')->param('offset', $offset + 1),
        ];
        if(!$offset) {
            $buttonRow = [
                Button::make('➡️')->action('goods')->param('offset', $offset + 1),
            ];
        }
       return $chat->photo($good->path_img)->markdown("*$good->name* \n\n$good->description")
            ->keyboard(Keyboard::make()
                ->row($buttonRow)
                ->row([
                    Button::make('🛒 Добавить в корзину')->action('shop')->param('idGood', $good->id)
                ])
                ->row([
                    Button::make('Назад')->action('catalog')
                ]));
    }
}
