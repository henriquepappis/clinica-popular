<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Shift\Models\Shift;
use App\Domain\Shift\Actions\CreateShiftAction;
use App\Domain\Shift\DataTransferObjects\ShiftData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function __construct(
        private CreateShiftAction $createAction,
    ) {}

    public function index(): JsonResponse
    {
        $shifts = Shift::active()->get();

        return response()->json([
            'data' => $shifts->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'period' => $s->period->label(),
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'max_appointments' => $s->max_appointments,
                'status' => $s->status->label(),
            ]),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period' => 'required|string|in:morning,afternoon,evening',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'required|integer|min:1',
        ]);

        $data = new ShiftData(
            name: $validated['name'],
            period: $validated['period'],
            startTime: $validated['start_time'],
            endTime: $validated['end_time'],
            maxAppointments: $validated['max_appointments'],
        );

        $shift = $this->createAction->execute($data);

        return response()->json([
            'message' => 'Turno criado com sucesso.',
            'data' => [
                'id' => $shift->id,
                'name' => $shift->name,
                'period' => $shift->period->label(),
                'status' => $shift->status->label(),
            ],
        ], 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $shift->id,
                'name' => $shift->name,
                'period' => $shift->period->label(),
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'max_appointments' => $shift->max_appointments,
                'status' => $shift->status->label(),
            ],
        ], 200);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'max_appointments' => 'integer|min:1',
        ]);

        $shift->update($validated);

        return response()->json([
            'message' => 'Turno atualizado com sucesso.',
            'data' => [
                'id' => $shift->id,
                'name' => $shift->name,
            ],
        ], 200);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Turno desativado com sucesso.',
        ], 200);
    }
}
