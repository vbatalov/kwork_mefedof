<?php

namespace App\Traits;

trait TelegramBotDataBuilderTrait
{
    public function build($action, $data = ""): string
    {
        return json_encode([
            "action" => $action,
            "data" => $data,
        ]);
    }

    public function decode(string $build): array
    {
        return json_decode($build, true);
    }
}