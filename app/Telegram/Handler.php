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
    private array $ids;

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
            $buttons[] = Button::make("$icons $group->name")->action('goods')->param('id', $group->id);
        }
        $buttons[] = Button::make('↩️ В начало')->action('main');
        $this->chat->edit($this->messageId)->message('Выберете категорию')->keyboard(Keyboard::make()
        ->row($buttons)
        ->chunk(2))->send();
    }

    public function goods(): void
    {
        if($id = $this->data->get('id')) {
            cache(['id' => $id], 300);
        } else {
            $id = cache('id');
        }
        $builder = Good::query()->where('id_group', $id);

        // Удаляем предыдущее сообщение т.к заменить медиа сообщением не получается
        $this->chat->deleteMessage($this->messageId)->send();
        $offset = $this->data->get('offset');
        if($offset) {
            $good = $builder->offset($offset)->first();
            TelegramController::cardText(chat: $this->chat, good: $good, offset: $offset)->send();
        } else {
            $good = $builder->first();
            TelegramController::cardText(chat: $this->chat, good: $good)->send();
        }
    }

    private function goodsItem(int $idGroup) {
        if(isset($this->goods[$idGroup])) {
            $this->goods[$idGroup] = Good::where('id_group', $idGroup);
        }
        return $this->goods[$idGroup];
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
