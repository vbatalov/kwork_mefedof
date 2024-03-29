<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Traits\TelegramBotTrait;
use Log;
use TelegramBot\Api\Exception;
use TelegramBot\Api\HttpException;

use TelegramBot\Api\Types\ArrayOfBotCommand;

use Throwable;


class TelegramApi extends Controller
{
    use TelegramBotTrait;

    public function setCommand()
    {
        $array = ArrayOfBotCommand::fromResponse(
            [
                [
                    "command" => "start",
                    "description" => "Запустить",
                ],
                [
                    "command" => "register",
                    "description" => "Зарегистрироваться",
                ],
                [
                    "command" => "restore_access",
                    "description" => "Восстановить доступ",
                ],
                [
                    "command" => "support",
                    "description" => "Поддержка",
                ],

            ],
        );
        try {
            $this->bot->setMyCommands($array);
            return true;
        } catch (HttpException | Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function register_bot()
    {
        $this->bot->setWebhook(url: config("app.BOT_URL"), dropPendingUpdates: true);
        return true;
    }

    public function getWebhookInfo()
    {
        return print_r($this->bot->getWebhookInfo());
    }


    public function index()
    {
        $command = new CommandController();
        $command->controller();

        try {
            $update = new UpdateController();
            $update->controller();
        } catch (Throwable $t) {
            print_r($t->getMessage());
            Log::error($t->getMessage());
        }
    }
}
