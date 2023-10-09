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
use App\Models\Good;
use App\Models\Group;
use DefStudio\Telegraph\Exceptions\TelegraphException;
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
        TelegramController::mainText(chat: $this->chat, firstName: $this->firstName())->send();
    }

    /**
     * Будет выполняться при возвращении на главную
     * @throws TelegraphException
     */
    public function main(): void
    {
        TelegramController::mainText(chat: $this->chat, firstName: $this->firstName())
            ->edit($this->messageId)
            ->send();
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
            $buttons[] = Button::make("$icons $group->name")->action('group')->param('id', $group->id)->param('messageId', $this->messageId);
        }
        $buttons[] = Button::make('↩️ В начало')->action('main');
        $this->chat->edit($this->messageId)->message('Выберете категорию')->keyboard(Keyboard::make()
        ->row($buttons)
        ->chunk(2))->send();
    }

    public function group()
    {
        $id = $this->data->get('id');
        $messageId = $this->data->get('messageId');
        $good = Good::where('id_group', $id)->limit(1)->first();

        // Удаляем предыдущее сообщение т.к заменить медиа сообщением не получается
        $this->chat->deleteMessage($this->messageId)->send();
        $this->chat->photo($good->path_img)->markdown("$good->name")
            ->keyboard(Keyboard::make()
                ->row([
                    Button::make('⬅️')->action('prev'),
                    Button::make('➡️')->action('next'),
                ])
                ->row([
                    Button::make('🛒 Добавить в корзину')->action('shop')->param('idGood', $good->id)
                ])
                ->row([
                    Button::make('Назад')->action('catalog')
                ]))->send();
//        foreach ($goods as $good) {
//            $this->chat
//                ->edit($this->messageId)
//                ->photo(Storage::path($good->path_img))
//                ->message($good->name)->send();
//        }
    }

    /**
     * Получение имени пользователя
     * @throws TelegraphException
     */
    private function firstName(): string
    {
        if(!isset($this->firstName)) {
            $info = $this->chat->info();
            $this->firstName = (string) $info['first_name'] ?? 'Пользователь';
        }
        return $this->firstName;
    }

}
