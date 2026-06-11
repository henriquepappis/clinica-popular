<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Specialty\Models\Specialty;
use App\Domain\Specialty\Actions\CreateSpecialtyAction;
use App\Domain\Specialty\DataTransferObjects\SpecialtyData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SpecialtyController extends Controller
{
    public function __construct(
        private CreateSpecialtyAction $createAction,
    ) {}

    public function index(): JsonResponse
    {
        $specialties = Specialty::active()->get();

        return response()->json([
            'data' => $specialties->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'status' => $s->status->label(),
            ]),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:specialties',
            'description' => 'nullable|string|max:500',
        ]);

        $data = new SpecialtyData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
        );

        $specialty = $this->createAction->execute($data);

        return response()->json([
            'message' => 'Especialidade criada com sucesso.',
            'data' => [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'description' => $specialty->description,
                'status' => $specialty->status->label(),
            ],
        ], 201);
    }

    public function show(Specialty $specialty): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'description' => $specialty->description,
                'status' => $specialty->status->label(),
            ],
        ], 200);
    }

    public function update(Request $request, Specialty $specialty): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:100|unique:specialties,name,' . $specialty->id,
            'description' => 'nullable|string|max:500',
        ]);

        $specialty->update($validated);

        return response()->json([
            'message' => 'Especialidade atualizada com sucesso.',
            'data' => [
                'id' => $specialty->id,
                'name' => $specialty->name,
            ],
        ], 200);
    }

    public function destroy(Specialty $specialty): JsonResponse
    {
        $specialty->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Especialidade desativada com sucesso.',
        ], 200);
    }
}
