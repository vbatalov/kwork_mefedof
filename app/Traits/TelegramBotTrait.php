<?php

namespace App\Traits;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

trait TelegramBotTrait
{
    public BotApi $bot;
    public Client $client;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->bot = new BotApi(config("app.BOT_TOKEN"));
        $this->client = new Client(config("app.BOT_TOKEN"));
    }
}