<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceRequest;
use App\Http\Requests\UpdatePriceRequest;
use App\Domain\Price\Models\Price;
use App\Domain\Price\Actions\CreatePriceAction;
use App\Domain\Price\Actions\UpdatePriceAction;
use App\Domain\Price\Actions\DeletePriceAction;
use App\Domain\Price\DataTransferObjects\PriceData;
use App\Domain\Price\Exceptions\InvalidPriceException;
use App\Domain\Price\Exceptions\PriceNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PriceController extends Controller
{
    public function __construct(
        private CreatePriceAction $createAction,
        private UpdatePriceAction $updateAction,
        private DeletePriceAction $deleteAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $prices = Price::with(['doctor', 'specialty'])
            ->when($request->query('doctor_id'), fn ($q, $doctor_id) => $q->where('doctor_id', $doctor_id))
            ->when($request->query('specialty_id'), fn ($q, $specialty_id) => $q->where('specialty_id', $specialty_id))
            ->get();

        return response()->json([
            'data' => $prices->map(fn ($p) => $this->format($p)),
        ], 200);
    }

    public function store(StorePriceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $data = new PriceData(
                value: (float) $validated['value'],
                doctorId: $validated['doctor_id'] ?? null,
                specialtyId: $validated['specialty_id'] ?? null,
                durationMinutes: $validated['duration_minutes'] ?? null,
            );

            $price = $this->createAction->execute($data);

            return response()->json([
                'message' => 'Preço criado com sucesso.',
                'data' => $this->format($price->load(['doctor', 'specialty'])),
            ], 201);
        } catch (InvalidPriceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Price $price): JsonResponse
    {
        return response()->json([
            'data' => $this->format($price->load(['doctor', 'specialty'])),
        ], 200);
    }

    public function update(UpdatePriceRequest $request, Price $price): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $price->id,
                (float) $request->validated()['value']
            );

            return response()->json([
                'message' => 'Preço atualizado com sucesso.',
                'data' => $this->format($updated->load(['doctor', 'specialty'])),
            ], 200);
        } catch (PriceNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidPriceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Price $price): JsonResponse
    {
        try {
            $this->deleteAction->execute($price->id);

            return response()->json([
                'message' => 'Preço removido com sucesso.',
            ], 200);
        } catch (PriceNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function format(Price $price): array
    {
        return [
            'id' => $price->id,
            'type' => $price->type()->label(),
            'doctor' => $price->doctor?->name,
            'specialty' => $price->specialty?->name,
            'duration_minutes' => $price->duration_minutes,
            'value' => $price->value,
        ];
    }
}
