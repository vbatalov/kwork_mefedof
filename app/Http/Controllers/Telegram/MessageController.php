<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;

use App\Traits\CodempTratit;
use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;

use Log;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use Throwable;


class MessageController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotButtonTrait;

    use CodempTratit;

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

                    if ($this->checkUser($user->phone)) {
                        $this->bot->sendMessage(chatId: $cid, text: "Пользователь с таким номером телефона уже зарегистрирован.", parseMode: "HTML", replyMarkup: new ReplyKeyboardRemove());
                        $keyboard = new InlineKeyboardMarkup([
                            [
                                $this->sendRecoveryButton(),
                            ],
                            [
                                $this->supportButton(),
                            ]
                        ]);
                        return $this->bot->sendMessage(chatId: $cid, text: "Используйте кнопку Восстановить доступ для получения данных авторизации.", parseMode: "HTML", replyMarkup: $keyboard);
                    }

                    //Запрос эл. почты
                    $message = view("TelegramBot.requestEmail")->render();
                    return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: new ReplyKeyboardRemove());
                } else {
                    return $this->bot->sendMessage(chatId: $cid, text: "Вы отправили чужой контактный номер.");
                }
            } else {
                if ($message->getText() != "/register") {
                    return $this->bot->sendMessage(chatId: $cid, text: "Используйте кнопку Отправить телефон, чтобы поделиться контактом.");
                }
            }
        }

        if ($user->cookie == "requestEmail") {
            $email = $message->getText();

            // Обновляю адрес эл. почты
            $user->update(["email" => $email]);
            try {
            $message = view("TelegramBot._requestEmailConfirmAddress", compact("email"))->render();
            $keyboard = new InlineKeyboardMarkup(
                [
                    [
                        $this->confirmEmail(),
                    ]
                ]
            );

                return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
            } catch (Throwable $exception) {
                print_r($exception->getMessage());
                Log::error($exception->getMessage());
            }
        }

        if (
            ($message->getText() != "/start")
            and ($message->getText() != "/support")
            and ($message->getText() != "/register")
            and ($message->getText() != "/restore_access")
        ) {
            $message = view("TelegramBot._errorAfterUserSendJustMessage")->render();
            $keyboard = new InlineKeyboardMarkup(
                [
                    [
                        $this->supportButton(),
                    ]
                ]
            );
            return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
        }

        return true;
    }
}
