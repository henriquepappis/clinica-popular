<?php

namespace App\Domain\Specialty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domain\Specialty\Enums\SpecialtyStatus;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Doctor\Models\DoctorSpecialty;
use Database\Factories\SpecialtyFactory;

class Specialty extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return SpecialtyFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => SpecialtyStatus::class,
    ];

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specialties')
            ->using(DoctorSpecialty::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', SpecialtyStatus::ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === SpecialtyStatus::ACTIVE;
    }
}