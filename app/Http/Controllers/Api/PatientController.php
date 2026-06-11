<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Actions\CreatePatientAction;
use App\Domain\Patient\DataTransferObjects\PatientData;
use App\Domain\Patient\Exceptions\InvalidCpfException;
use App\Domain\Patient\Exceptions\DuplicatePatientException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PatientController extends Controller
{
    public function __construct(
        private CreatePatientAction $createAction,
    ) {}

    public function index(): JsonResponse
    {
        $patients = Patient::all();

        return response()->json([
            'data' => $patients->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'cpf' => $p->cpf,
                'phone' => $p->phone,
                'status' => $p->status->label(),
            ]),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'cpf' => 'required|string|unique:patients',
                'birth_date' => 'required|date_format:Y-m-d',
                'phone' => 'required|string',
            ]);

            $data = new PatientData(
                name: $validated['name'],
                cpf: $validated['cpf'],
                birthDate: Carbon::createFromFormat('Y-m-d', $validated['birth_date']),
                phone: $validated['phone'],
            );

            $patient = $this->createAction->execute($data);

            return response()->json([
                'message' => 'Paciente criado com sucesso.',
                'data' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'cpf' => $patient->cpf,
                    'phone' => $patient->phone,
                    'status' => $patient->status->label(),
                ],
            ], 201);
        } catch (InvalidCpfException | DuplicatePatientException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Patient $patient): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'cpf' => $patient->cpf,
                'birth_date' => $patient->birth_date->format('Y-m-d'),
                'phone' => $patient->phone,
                'status' => $patient->status->label(),
            ],
        ], 200);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string',
        ]);

        $patient->update($validated);

        return response()->json([
            'message' => 'Paciente atualizado com sucesso.',
            'data' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
            ],
        ], 200);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->deactivate();

        return response()->json([
            'message' => 'Paciente desativado com sucesso.',
        ], 200);
    }
}
