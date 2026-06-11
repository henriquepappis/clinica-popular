<?php

namespace App\Domain\Price\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Price\Enums\PriceType;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;
use Database\Factories\PriceFactory;

class Price extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory(): PriceFactory
    {
        return PriceFactory::new();
    }

    protected $fillable = [
        'doctor_id',
        'specialty_id',
        'duration_minutes',
        'value',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'value' => 'decimal:2',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForSpecialty($query, $specialtyId)
    {
        return $query->whereNull('doctor_id')->where('specialty_id', $specialtyId);
    }

    public function scopeForDuration($query, $durationMinutes)
    {
        return $query->where('duration_minutes', $durationMinutes);
    }

    public function type(): PriceType
    {
        if ($this->doctor_id) {
            return PriceType::DOCTOR;
        }

        if ($this->specialty_id) {
            return PriceType::SPECIALTY;
        }

        return PriceType::DURATION;
    }
}
