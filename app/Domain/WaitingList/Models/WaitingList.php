<?php

namespace App\Domain\WaitingList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\WaitingList\Enums\WaitingListStatus;
use App\Domain\Patient\Models\Patient;
use App\Domain\Specialty\Models\Specialty;
use Database\Factories\WaitingListFactory;

class WaitingList extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory(): WaitingListFactory
    {
        return WaitingListFactory::new();
    }

    protected $fillable = [
        'patient_id',
        'specialty_id',
        'priority',
        'status',
        'reason',
        'added_at',
        'notified_at',
    ];

    protected $casts = [
        'status' => WaitingListStatus::class,
        'added_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', WaitingListStatus::WAITING);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority)->orderBy('added_at');
    }

    public function scopeBySpecialty($query, $specialtyId)
    {
        return $query->where('specialty_id', $specialtyId);
    }

    public function isWaiting(): bool
    {
        return $this->status === WaitingListStatus::WAITING;
    }

    public function isPriority(): bool
    {
        return $this->priority >= 3;
    }
}
