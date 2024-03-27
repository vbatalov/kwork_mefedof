<?php

namespace App\Traits;

use App\Models\Telegram\TelegramUser;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;


trait CodempTratit
{

    public function checkUser(string $phone): bool
    {
        $url = "https://api.codemp.ru/v1/telegram/bd7c943a-d8da-42e3-ac82-25976c7d9c27/check-user";
        $response = Http::withHeaders([
            "Accept" => "application/json"
        ])
            ->post($url,
                [
                    "phone" => $phone
                ]);

        if ($response->status() == 200) {
            $response = json_decode($response->body(), true);
            return $response['data']['is_exists'] == true;
        }

        return false;
    }


    public function createUser(TelegramUser $user): Application|Response|\Illuminate\Contracts\Foundation\Application|ResponseFactory
    {
        $url = "https://api.codemp.ru/v1/telegram/bd7c943a-d8da-42e3-ac82-25976c7d9c27/create-user";
        $response = Http::withHeaders([
            "Accept" => "application/json"
        ])
            ->post($url,
                [
                    "email" => $user->email,
                    "nickname" => $user->username,
                    "phone" => $user->phone,
                    "cid" => $user->cid,
                ]);

        if ($response->status() == 200) {
            $response = json_decode($response->body(), true);
            return response($response['data']);
        } else {
            $response = json_decode($response->body(),true);
            return response($response['message'], 422);
        }
    }

    public function recoveryPassword(TelegramUser $user): Application|Response|\Illuminate\Contracts\Foundation\Application|ResponseFactory
    {
        $url = "https://api.codemp.ru/v1/telegram/bd7c943a-d8da-42e3-ac82-25976c7d9c27/recovery-password";
        $response = Http::withHeaders([
            "Accept" => "application/json"
        ])
            ->post($url,
                [
                    "phone" => $user->phone,
                ]);

        if ($response->status() == 200) {
            $response = json_decode($response->body(), true);
            return response($response['data']);
        } else {
            $response = json_decode($response->body(),true);
            return response($response['message'], 422);
        }
    }
}