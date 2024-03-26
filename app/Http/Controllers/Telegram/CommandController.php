<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;
use App\Traits\TelegramBotDataBuilderTrait;
use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;
use Illuminate\Http\Request;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;

class CommandController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotDataBuilderTrait;
    use TelegramBotButtonTrait;

    public function controller()
    {
        try {
            //Handle /start command
            $this->client->command('start', function (Message $message) {
                $cid = $message->getChat()->getId();
                $last_name = $message->getChat()->getLastName();
                $first_name = $message->getChat()->getFirstName();
                $username = $message->getChat()->getUsername();

                TelegramUser::updateOrCreate(
                    [
                        "cid" => $cid,
                    ],
                    [
                        "last_name" => $last_name ?? null,
                        "first_name" => $first_name ?? null,
                        "username" => $username ?? null,
                        "cookie" => "start",
                    ]
                );

                $message = view("TelegramBot.startCommand")->render();
                $keyboard = new InlineKeyboardMarkup(
                    [
                        [
                            $this->howItWorkButton(),
                            $this->registerButton(),
                        ]
                    ]
                );
                return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML",replyMarkup: $keyboard);
            });

            return $this->client->run();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
