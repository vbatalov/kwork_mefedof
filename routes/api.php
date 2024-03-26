<?php

use App\Http\Controllers\Telegram\TelegramApi;
use Illuminate\Support\Facades\Route;

Route::post("telegram_bot", [TelegramApi::class, "index"]);
