<?php

namespace App\Domain\Auth\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DOCTOR = 'doctor';
    case RECEPTIONIST = 'receptionist';
    case PATIENT = 'patient';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::DOCTOR => 'Médico',
            self::RECEPTIONIST => 'Recepcionista',
            self::PATIENT => 'Paciente',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => ['*'], // Todas as permissões
            self::DOCTOR => ['view_appointments', 'update_own_appointments', 'view_patients'],
            self::RECEPTIONIST => ['manage_appointments', 'manage_patients', 'manage_payments'],
            self::PATIENT => ['view_own_appointments', 'create_appointments'],
        };
    }
}
