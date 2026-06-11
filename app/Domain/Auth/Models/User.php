<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Domain\Auth\Enums\UserRole;
use Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, HasFactory, Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'bio',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isDoctor(): bool
    {
        return $this->role === UserRole::DOCTOR;
    }

    public function isReceptionist(): bool
    {
        return $this->role === UserRole::RECEPTIONIST;
    }

    public function isPatient(): bool
    {
        return $this->role === UserRole::PATIENT;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->role->permissions();

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
}
