<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;

use App\Traits\TelegramBotDataBuilderTrait;
use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;

use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;


class CallbackController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotDataBuilderTrait;
    use TelegramBotButtonTrait;

    /**
     * @throws Exception
     */
    public function controller(CallbackQuery $callback)
    {

        $data = $this->decode($callback->getData());

        if ($data["action"] == "register") {
            $this->register($callback);
        }

        if ($data["action"] == "howItWork") {
            $this->howItWork($callback);
        }

        if ($data["action"] == "_requestEmailUserConfirm") {
            $this->_requestEmailUserConfirm($callback);
        }

        return $this->bot->answerCallbackQuery($callback->getId());
    }

    /**
     * @throws Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    private function register(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();

        TelegramUser::where("cid", $cid)->firstOrFail()->update([
            "cookie" => "requestPhone"
        ]);

        $message = view("TelegramBot.register")->render();
        $keyboard = new ReplyKeyboardMarkup(
            keyboard: [
            [
                $this->sendPhone(),
            ]
        ],
            oneTimeKeyboard: true, resizeKeyboard: true,
        );

        $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
    }

    /**
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    private function howItWork(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();

        $keyboard = new InlineKeyboardMarkup(
            [
                [
                    $this->registerButton(),
                ]
            ]
        );
        $message = view("TelegramBot.howItWork")->render();
        $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
    }

    /**
     * @throws Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    private function _requestEmailUserConfirm(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();

        $this->bot->sendMessage(chatId: $cid, text: "ПРЕДВАРИТЕЛЬНО: Как пользователь должен подтвердить почту?", parseMode: "HTML");

    }
}
