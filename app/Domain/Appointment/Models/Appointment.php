<?php

namespace App\Domain\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Patient\Models\Patient;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Shift\Models\Shift;
use Database\Factories\AppointmentFactory;

class Appointment extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return AppointmentFactory::new();
    }

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'shift_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'status' => AppointmentStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            AppointmentStatus::SCHEDULED,
            AppointmentStatus::CONFIRMED,
        ]);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('appointment_date', $date);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isConfirmed(): bool
    {
        return $this->status === AppointmentStatus::CONFIRMED;
    }
}