<?php

namespace App\Services;

use App\Mail\TwoFactorCodeMail;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorService
{
    private const CODE_LENGTH = 6;
    private const EXPIRES_MINUTES = 10;

    public function send(User $user): void
    {
        $code = $this->generateCode();

        TwoFactorCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            ]
        );

        Mail::to($user->email)->send(new TwoFactorCodeMail($code, self::EXPIRES_MINUTES));
    }

    private function generateCode(): string
    {
        $min = (int) (10 ** (self::CODE_LENGTH - 1));
        $max = (int) ((10 ** self::CODE_LENGTH) - 1);

        return (string) random_int($min, $max);
    }
}
