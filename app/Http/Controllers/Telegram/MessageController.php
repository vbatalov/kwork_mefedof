<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;

use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardRemove;


class MessageController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotButtonTrait;

    public function controller(Message $message)
    {
        $cid = $message->getChat()->getId();
        $user = TelegramUser::where("cid", $cid)->firstOrFail();

        if ($user->cookie == "requestPhone") {
            if ($contact = $message->getContact()) {
                if ($contact->getUserId() == $cid) {
                    // Обновляю контактный номер
                    $user->update(
                        [
                            "phone" => $contact->getPhoneNumber(),
                            "cookie" => "requestEmail"
                        ]);

                    //Запрос эл. почты
                    $message = view("TelegramBot.requestEmail")->render();
                    return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: new ReplyKeyboardRemove());
                } else {
                    return $this->bot->sendMessage(chatId: $cid, text: "Вы отправили чужой контактный номер.");
                }
            } else {
                return $this->bot->sendMessage(chatId: $cid, text: "Используйте кнопку Отправить телефон, чтобы поделиться контактом.");
            }
        }

        if ($user->cookie == "requestEmail") {
            $email = $message->getText();

            // Обновляю адрес эл. почты
            $user->update(["email" => $email]);

            $message = view("TelegramBot._requestEmailConfirmAddress", compact("email"))->render();
            $keyboard = new InlineKeyboardMarkup(
                [
                    [
                        $this->confirmEmail(email: $email),
                    ]
                ]
            );
            return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
        }

        return true;
    }
}
