<?php

namespace App\Traits;


use JetBrains\PhpStorm\ArrayShape;

trait TelegramBotButtonTrait
{
    use TelegramBotDataBuilderTrait;


    #[ArrayShape(['text' => "string", 'callback_data' => "string"])]
    public function registerButton(): array
    {
        return ['text' => '✅ Зарегистрироваться', 'callback_data' => $this->build(action: "register")];
    }

    #[ArrayShape(['text' => "string", 'callback_data' => "string"])]
    public function howItWorkButton(): array
    {
        return ['text' => '🧐 Как это работает?', 'callback_data' => $this->build(action: "howItWork")];
    }

    #[ArrayShape(['text' => "string", 'request_contact' => "bool"])]
    public function sendPhone(): array
    {
        return ['text' => '📱 Отправить телефон', 'request_contact' => true];
    }

    #[ArrayShape(['text' => "string", 'callback_data' => "string"])]
    public function confirmEmail($email): array
    {
        return ['text' => 'Подтверждаю', 'callback_data' => $this->build(action: "confirmAndRegister", data: $email)];
    }

    #[ArrayShape(['text' => "string", 'url' => "string"])]
    public function goToWebsite(string $text, string $url): array
    {
        return ['text' => $text, 'url' => $url];
    }

    #[ArrayShape(['text' => "string", 'url' => "string"])]
    public function supportButton(): array
    {
        return ['text' => "😎 Поддержка", 'url' => "https://t.me/vel1122"];
    }

    #[ArrayShape(['text' => "string", 'callback_data' => "string"])]
    public function sendRecoveryButton(): array
    {
        return ['text' => 'Восстановить доступ', 'callback_data' => $this->build(action: "recoveryPassword")];
    }
}