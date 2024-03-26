<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $fillable =
        [
            "cid",
            "first_name",
            "last_name",
            "username",
            "cookie",
            "email",
            "phone",
        ];
}
