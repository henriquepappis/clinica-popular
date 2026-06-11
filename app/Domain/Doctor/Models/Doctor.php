<?php

namespace App\Domain\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domain\Doctor\Enums\DoctorStatus;
use App\Domain\Specialty\Models\Specialty;
use Database\Factories\DoctorFactory;

class Doctor extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return DoctorFactory::new();
    }

    protected $fillable = [
        'name',
        'crm',
        'phone',
        'email',
        'bio',
        'status',
    ];

    protected $casts = [
        'status' => DoctorStatus::class,
    ];

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialties')
            ->using(DoctorSpecialty::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', DoctorStatus::ACTIVE);
    }

    public function scopeBySpecialty($query, $specialtyId)
    {
        return $query->whereHas('specialties', function ($q) use ($specialtyId) {
            $q->where('specialties.id', $specialtyId);
        });
    }

    public function isActive(): bool
    {
        return $this->status === DoctorStatus::ACTIVE;
    }

    public function hasSpecialty(string $specialtyId): bool
    {
        return $this->specialties()->where('specialties.id', $specialtyId)->exists();
    }
}
