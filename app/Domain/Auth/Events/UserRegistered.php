<?php

namespace App\Domain\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Auth\Models\User;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user
    ) {}
}
