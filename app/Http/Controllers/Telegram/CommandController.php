<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;
use App\Traits\TelegramBotDataBuilderTrait;
use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;
use Illuminate\Http\Request;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

class CommandController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotDataBuilderTrait;
    use TelegramBotButtonTrait;

    public function controller()
    {
        try {
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
                            $this->registerButton(),

                        ],
                        [
                            $this->howItWorkButton(),
                        ]
                    ]
                );
                return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
            });

            $this->client->command('support', function (Message $message) {
                $cid = $message->getChat()->getId();

                $message = view("TelegramBot.__commandSupport")->render();
                $keyboard = new InlineKeyboardMarkup(
                    [
                        [
                            $this->supportButton(),
                        ]
                    ]
                );
                return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
            });

            $this->client->command('register', function (Message $message) {
                $cid = $message->getChat()->getId();
                $user = TelegramUser::where("cid", $cid)->firstOrFail();

                $callbackController = new CallbackController();
                if ($callbackController->_checkUserExists(user: $user) == true) {
                    $keyboard = new InlineKeyboardMarkup([
                        [
                            $this->sendRecoveryButton()
                        ]
                    ]);
                    $message = view("TelegramBot._userAlreadyExistsInDB");
                    return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);

                } else {
                    $user->update([
                        "cookie" => "requestPhone",
                    ]);
                    $message = view("TelegramBot.register")->render();
                    $keyboard = new ReplyKeyboardMarkup(
                        [
                            [
                                $this->sendPhone(),
                            ]
                        ],
                        true, true,
                    );
                    return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
                }

            });

            $this->client->command('restore_access', function (Message $message) {

                $cid = $message->getChat()->getId();

                $user = TelegramUser::where("cid", $cid)->firstOrFail();
                $user->update([
                    "cookie" => "restore_access"
                ]);

                if (empty($user->phone)) {
                    $message = view("TelegramBot.__commandRestoreAccessErrorPhoneEmptyOrNotFound");
                    return $this->bot->sendMessage(chatId: $cid, text: "$message", parseMode: "HTML", replyMarkup: new ReplyKeyboardRemove());
                } else {
                    $callbackController = new CallbackController();
                    if ($callbackController->_checkUserExists(user: $user) == true) {
                        $message = view("TelegramBot.__commandRestoreAccessSendRestoreButton");
                        $keyboard = new InlineKeyboardMarkup([
                            [
                                $this->sendRecoveryButton()
                            ]
                        ]);
                        return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
                    } else {
                        $keyboard = new InlineKeyboardMarkup([
                            [
                                $this->registerButton(),
                            ]
                        ]);
                        $message = view("TelegramBot.__commandRestoreAccessErrorPhoneEmptyOrNotFound");
                        return $this->bot->sendMessage(chatId: $cid, text: "$message", parseMode: "HTML", replyMarkup: $keyboard);
                    }
                }

            });

            return $this->client->run();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
