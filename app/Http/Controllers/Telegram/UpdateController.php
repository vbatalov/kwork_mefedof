<?php

namespace App\Http\Controllers\Telegram;


use App\Traits\TelegramBotTrait;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;


class UpdateController
{
    use TelegramBotTrait;

    public function controller()
    {
        try {
            $this->client->on(function (Update $update) {
                $message = $update->getMessage() ?? $update->getCallbackQuery();

                /** Обработка Callback */
                if ($message instanceof CallbackQuery) {
                    $callbackController = new CallbackController();
                    $callbackController->controller(callback: $message);
                }

                /** Обработка входящих сообщений */
                if ($message instanceof Message) {
                    $messageController = new MessageController();
                    $messageController->controller(message: $message);
                }

            }, function () {
                return true;
            });

            return $this->client->run();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
