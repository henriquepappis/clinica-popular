<?php

namespace App\Domain\Shift\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Shift\Enums\ShiftPeriod;
use App\Domain\Shift\Enums\ShiftStatus;
use Database\Factories\ShiftFactory;

class Shift extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return ShiftFactory::new();
    }

    protected $fillable = [
        'name',
        'period',
        'start_time',
        'end_time',
        'max_appointments',
        'status',
    ];

    protected $casts = [
        'period' => ShiftPeriod::class,
        'status' => ShiftStatus::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', ShiftStatus::ACTIVE);
    }

    public function scopeByPeriod($query, ShiftPeriod $period)
    {
        return $query->where('period', $period);
    }

    public function isActive(): bool
    {
        return $this->status === ShiftStatus::ACTIVE;
    }

    public function isFull(): bool
    {
        return $this->appointments_count >= $this->max_appointments;
    }
}