<?php
/*
* @name        DARKLYY
* @link        https://darklyy.ru/
* @copyright   Copyright (C) 2012-2023 ООО «ПРИС»
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      Alexander (26.09.2023 8:11)
*/

namespace App\Telegram;

use App\Models\Group;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;


class Handler extends WebhookHandler
{


    /**
     * Запуск телеграмм бота
     * @return void
     */
    public function start(): void
    {
        $info = $this->chat->info();
        $firstName = (string) $info['first_name'];
        if($firstName) {
            $message = "Здравствуйте *". $firstName . "!* Прошу ознакомиться с возможностями данного бота!";
            $this->chat->markdown($message)->keyboard(Keyboard::make()
                ->row([
                    Button::make('🛒 Каталог')->action('catalog'),
                    Button::make('🔎 Поиск')->action('search'),
                    Button::make('❕ Информация')->action('read')->param('id', $this->chat->chat_id),
                    Button::make('📢 Поддержка')->url('https://t.me/epccep'),
                ])
                ->chunk(2)
                ->row([
                    Button::make('🛍️ Корзина')->action('read')->param('id', $this->chat->chat_id),
                ])
                ->row([
                    Button::make('📦 Мои заказы')->action('read')->param('id', $this->chat->chat_id),
                ]))->send();
        }
    }

    public function catalog()
    {
        $catalog = Group::all();
        $buttons = [];
        foreach ($catalog as $category) {
            $buttons[] = Button::make($category->name)->action($category->action);
        }
        $buttons[] = Button::make('↩️ В начало')->action('start');
        $this->chat->edit($this->messageId)->message('Выберете категорию')->keyboard(Keyboard::make()
        ->row($buttons)
        ->chunk(2))->send();

    }

}
