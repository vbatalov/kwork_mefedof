<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Telegram\TelegramUser;

use App\Traits\CodempTratit;
use App\Traits\TelegramBotDataBuilderTrait;
use App\Traits\TelegramBotButtonTrait;
use App\Traits\TelegramBotTrait;

use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;


class CallbackController extends Controller
{
    use TelegramBotTrait;
    use TelegramBotDataBuilderTrait;
    use TelegramBotButtonTrait;

    use CodempTratit;

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

        if ($data["action"] == "confirmAndRegister") {
            $this->_confirmAndRegister($callback);
        }

        if ($data['action'] == "recoveryPassword") {
            $this->_requestRecoveryPassword($callback);
        }

        return $this->bot->answerCallbackQuery($callback->getId());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function register(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();

        // init user model
        $user = TelegramUser::where("cid", $cid)->firstOrFail();

        /**
         * Если пользователь запустил бот повторно, вероятно он оставил свой телефон раньше
         * Если телефона у пользователя нет, продолжаю собирать данные
         * Если телефон у пользователя есть, отправляю API на проверку пользователя в БД
         * @return true предлагает пользователю восстановить пароль
         * @return false продолжает собирать данные пользователя
         */

        if (!empty($user->phone)) {
            if ($this->_checkUserExists($user) == true) {
                return $this->userAlreadyExists(callbackQuery: $callbackQuery);
            }
        }

        $user->update([
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

        return $this->bot->sendMessage(chatId: $cid, text: $message, parseMode: "HTML", replyMarkup: $keyboard);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    private function _confirmAndRegister(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();
        // init user
        $user = TelegramUser::where("cid", $cid)->firstOrFail();
        // api create user
        $response = $this->createUser(user: $user);

        // Если пользователь создан, отправляю данные для входа
        if ($response->status() == 200) {
            $data = json_decode($response->content(), true);
            $this->__sendLoginData(callbackQuery: $callbackQuery, cid: $cid, data: $data);
        } else {
            // Если пользователь уже существует
            $keyboard = new InlineKeyboardMarkup([
                [
                    $this->sendRecoveryButton(),
                ]
            ]);
            $this->bot->editMessageText(chatId: $cid,
                messageId: $callbackQuery->getMessage()->getMessageId(),
                text: $response->content(),
                parseMode: "HTML", replyMarkup: $keyboard);

        }
    }


    /** API: Проверяет зарегистрирован ли пользователь в системе
     * @return true направить сообщение о восстановлении пароля
     * @return false продолжить регистрацию пользователя, сбор данных
     */
    private function _checkUserExists(TelegramUser $user)
    {
        return $this->checkUser(phone: $user->phone);
    }

    /** API: Если пользователь уже зарегистрирован, предлагаем восстановить доступ */
    private function userAlreadyExists(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();

        $keyboard = new InlineKeyboardMarkup([
            [
                $this->sendRecoveryButton(),
            ]
        ]);
        $message = view("TelegramBot._userAlreadyExistsInDB")->render();
        $this->bot->editMessageText(chatId: $cid,
            messageId: $callbackQuery->getMessage()->getMessageId(),
            text: $message,
            parseMode: "HTML", replyMarkup: $keyboard);
    }

    /** Пользователь запросил восстановление пароля */
    private function _requestRecoveryPassword(CallbackQuery $callbackQuery)
    {
        $cid = $callbackQuery->getMessage()->getChat()->getId();
        $response = $this->recoveryPassword(user: TelegramUser::where("cid", $cid)->firstOrFail());
        if ($response->status() == 200) {
            $data = json_decode($response->content(), true);
            $this->__sendLoginData(callbackQuery: $callbackQuery, cid: $cid, data: $data);
        } else {
            $this->bot->editMessageText(chatId: $cid,
                messageId: $callbackQuery->getMessage()->getMessageId(),
                text: $response->original, parseMode: "HTML");
        }
    }

    private function __sendLoginData(CallbackQuery $callbackQuery, string $cid, array $data)
    {
        $message = view("TelegramBot._sendLoginData", compact("data"))->render();
        $keyboard = new InlineKeyboardMarkup([
            [
                $this->goToWebsite("Войти в личный кабинет", "https://codemp.ru/login")
            ]
        ]);
        $this->bot->editMessageText(chatId: $cid,
            messageId: $callbackQuery->getMessage()->getMessageId(),
            text: "$message", parseMode: "HTML", replyMarkup: $keyboard);
    }

}
