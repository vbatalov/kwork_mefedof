<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Telegram\TelegramUser;
use App\Traits\CodempTratit;
use Tests\TestCase;

class CodempApiTest extends TestCase
{
   use CodempTratit;

    public function test_checkUser(): void
    {
        $notFound = $this->checkUser("+79131008000");
        $this->assertFalse($notFound);

        $found = $this->checkUser("79999999990");
        $this->assertTrue($found);
    }
    public function test_createUser(): void
    {
        $user_exists = $this->createUser(TelegramUser::find(1));
        dd($user_exists);
    }

    public function test_recoveryPassword()
    {
        $response = $this->recoveryPassword(TelegramUser::find(1));
        dd($response->original);
    }
}
