<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\DataTransferObjects\LoginData;
use App\Domain\Auth\Events\UserLoggedIn;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    public function execute(LoginData $data): array
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if (!$user->is_active) {
            throw new InvalidCredentialsException('Usuário inativo.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        event(new UserLoggedIn($user));

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
