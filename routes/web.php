<?php

use App\Http\Controllers\Telegram\TelegramApi;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("register_bot", [TelegramApi::class, "register_bot"]);
Route::get("getWebhookInfo", [TelegramApi::class, "getWebhookInfo"]);