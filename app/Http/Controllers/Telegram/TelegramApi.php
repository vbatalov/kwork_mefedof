<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Traits\TelegramBotTrait;


class TelegramApi extends Controller
{
    use TelegramBotTrait;

    public function register_bot()
    {
        $this->bot->setWebhook(url: config("app.BOT_URL"), dropPendingUpdates: true);
        return "Webhook was set";
    }
    public function getWebhookInfo()
    {
        dd($this->bot->getWebhookInfo());
    }


    public function index()
    {
        try {
            $command = new CommandController();
            $command->controller();

            $update = new UpdateController();
            $update->controller();
        } catch (\Throwable $t) {
            print_r($t->getMessage());
            \Log::error($t->getMessage());
        }
    }
}
