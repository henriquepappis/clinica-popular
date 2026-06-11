<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\DataTransferObjects\RegisterData;
use App\Domain\Auth\Events\UserRegistered;
use App\Domain\Auth\Exceptions\UserAlreadyExistsException;
use App\Domain\Auth\Enums\UserRole;

class RegisterAction
{
    public function execute(RegisterData $data): User
    {
        if (User::where('email', $data->email)->exists()) {
            throw new UserAlreadyExistsException();
        }

        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'phone' => $data->phone,
            'role' => $data->role,
            'is_active' => true,
        ]);

        event(new UserRegistered($user));

        return $user;
    }
}
