<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Doctor\Actions\CreateDoctorAction;
use App\Domain\Doctor\DataTransferObjects\DoctorData;
use App\Domain\Doctor\Exceptions\SpecialtyNotFoundException;
use App\Domain\Doctor\Exceptions\DuplicateCrmException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(
        private CreateDoctorAction $createAction,
    ) {}

    public function index(): JsonResponse
    {
        $doctors = Doctor::active()->with('specialties')->get();

        return response()->json([
            'data' => $doctors->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'crm' => $d->crm,
                'specialties' => $d->specialties->pluck('name'),
                'email' => $d->email,
                'phone' => $d->phone,
                'status' => $d->status->label(),
            ]),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'crm' => 'required|string|unique:doctors|regex:/^\d{4,6}$/',
                'specialty_ids' => 'required|array|min:1',
                'specialty_ids.*' => 'uuid',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'bio' => 'nullable|string',
            ]);

            $data = new DoctorData(
                name: $validated['name'],
                crm: $validated['crm'],
                specialtyIds: $validated['specialty_ids'],
                email: $validated['email'] ?? null,
                phone: $validated['phone'] ?? null,
                bio: $validated['bio'] ?? null,
            );

            $doctor = $this->createAction->execute($data);

            return response()->json([
                'message' => 'Médico criado com sucesso.',
                'data' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'crm' => $doctor->crm,
                    'specialties' => $doctor->specialties->pluck('name'),
                    'status' => $doctor->status->label(),
                ],
            ], 201);
        } catch (SpecialtyNotFoundException | DuplicateCrmException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'crm' => $doctor->crm,
                'specialties' => $doctor->specialties->pluck('name'),
                'email' => $doctor->email,
                'phone' => $doctor->phone,
                'bio' => $doctor->bio,
                'status' => $doctor->status->label(),
            ],
        ], 200);
    }

    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);

        $doctor->update($validated);

        return response()->json([
            'message' => 'Médico atualizado com sucesso.',
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
            ],
        ], 200);
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        $doctor->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Médico desativado com sucesso.',
        ], 200);
    }
}
