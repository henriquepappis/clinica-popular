<?php

namespace App\Domain\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Patient\Enums\PatientStatus;
use Database\Factories\PatientFactory;

class Patient extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'patients';

    protected static function newFactory()
    {
        return PatientFactory::new();
    }

    protected $fillable = [
        'name',
        'cpf',
        'birth_date',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => PatientStatus::class,
        'birth_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', PatientStatus::ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', PatientStatus::INACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === PatientStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->update(['status' => PatientStatus::INACTIVE]);
    }

    public function activate(): void
    {
        $this->update(['status' => PatientStatus::ACTIVE]);
    }
}