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

use App\Http\Controllers\TelegramController;
use App\Models\Group;
use DefStudio\Telegraph\Exceptions\TelegraphException;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;


class Handler extends WebhookHandler
{


    /**
     * Запуск телеграмм бота
     * @return void
     * @throws TelegraphException
     */
    public function start(): void
    {
        $info = $this->chat->info();
        $firstName = (string) $info['first_name'];
        if($firstName) {
            TelegramController::mainText($this->chat, $firstName);
        }
    }

    /**
     * добавление иконок в бд utf8_encode("\u{1F600}")
     * вывод иконок из бд utf8_decode($icons)
     *
     * @return void
     */
    public function catalog(): void
    {
        $catalog = Group::all();
        $buttons = [];

        foreach ($catalog as $group) {
            $icons = utf8_decode($group->icons);
            $buttons[] = Button::make("$icons $group->name")->action('group')->param('id', $group->id);
        }
        $buttons[] = Button::make('↩️ В начало')->action('start')->param('edit', true);

        $this->chat->edit($this->messageId)->message('Выберете категорию')->keyboard(Keyboard::make()
        ->row($buttons)
        ->chunk(2))->send();
    }

}
