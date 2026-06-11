<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\Actions\CreateAppointmentAction;
use App\Domain\Appointment\Actions\ConfirmAppointmentAction;
use App\Domain\Appointment\Actions\CancelAppointmentAction;
use App\Domain\Appointment\DataTransferObjects\AppointmentData;
use App\Domain\Appointment\Exceptions\ShiftFullException;
use App\Domain\Appointment\Exceptions\DoctorUnavailableException;
use App\Domain\Appointment\Exceptions\AppointmentNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function __construct(
        private CreateAppointmentAction $createAction,
        private ConfirmAppointmentAction $confirmAction,
        private CancelAppointmentAction $cancelAction,
    ) {}

    public function index(): JsonResponse
    {
        $appointments = Appointment::with(['patient', 'doctor', 'shift'])
            ->active()
            ->get();

        return response()->json([
            'data' => $appointments->map(fn($a) => [
                'id' => $a->id,
                'patient' => $a->patient->name,
                'doctor' => $a->doctor->name,
                'appointment_date' => $a->appointment_date->format('Y-m-d'),
                'appointment_time' => $a->appointment_time,
                'status' => $a->status->label(),
            ]),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|uuid|exists:patients,id',
                'doctor_id' => 'required|uuid|exists:doctors,id',
                'shift_id' => 'required|uuid|exists:shifts,id',
                'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i',
                'notes' => 'nullable|string',
            ]);

            $data = new AppointmentData(
                patientId: $validated['patient_id'],
                doctorId: $validated['doctor_id'],
                shiftId: $validated['shift_id'],
                appointmentDate: $validated['appointment_date'],
                appointmentTime: $validated['appointment_time'],
                notes: $validated['notes'] ?? null,
            );

            $appointment = $this->createAction->execute($data);

            return response()->json([
                'message' => 'Agendamento criado com sucesso.',
                'data' => [
                    'id' => $appointment->id,
                    'patient' => $appointment->patient->name,
                    'doctor' => $appointment->doctor->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_time,
                    'status' => $appointment->status->label(),
                ],
            ], 201);
        } catch (ShiftFullException | DoctorUnavailableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $appointment->id,
                'patient' => $appointment->patient->name,
                'doctor' => $appointment->doctor->name,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'notes' => $appointment->notes,
                'status' => $appointment->status->label(),
            ],
        ], 200);
    }

    public function confirm(Appointment $appointment): JsonResponse
    {
        try {
            $confirmed = $this->confirmAction->execute($appointment->id);

            return response()->json([
                'message' => 'Agendamento confirmado com sucesso.',
                'data' => [
                    'id' => $confirmed->id,
                    'status' => $confirmed->status->label(),
                ],
            ], 200);
        } catch (AppointmentNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'nullable|string',
            ]);

            $cancelled = $this->cancelAction->execute(
                $appointment->id,
                $validated['reason'] ?? null
            );

            return response()->json([
                'message' => 'Agendamento cancelado com sucesso.',
                'data' => [
                    'id' => $cancelled->id,
                    'status' => $cancelled->status->label(),
                ],
            ], 200);
        } catch (AppointmentNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
