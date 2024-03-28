<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('bot:register', function () {
    $this->comment('Processing');
    $controller = new App\Http\Controllers\Telegram\TelegramApi();
    if ($controller->register_bot()) {
        $this->comment('Webhook was set');
    } else {
        $this->comment('Error set webhook');
    }
});

Artisan::command('bot:info', function () {
    $this->comment('Processing');

    $controller = new App\Http\Controllers\Telegram\TelegramApi();
    $this->comment($controller->getWebhookInfo());

    $this->comment('Complete');
});