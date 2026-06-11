<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWaitingListRequest;
use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\WaitingList\Actions\CreateWaitingListAction;
use App\Domain\WaitingList\Actions\NotifyWaitingListAction;
use App\Domain\WaitingList\Actions\RemoveFromWaitingListAction;
use App\Domain\WaitingList\DataTransferObjects\WaitingListData;
use App\Domain\WaitingList\Exceptions\PatientAlreadyInWaitingListException;
use App\Domain\WaitingList\Exceptions\WaitingListNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WaitingListController extends Controller
{
    public function __construct(
        private CreateWaitingListAction $createAction,
        private NotifyWaitingListAction $notifyAction,
        private RemoveFromWaitingListAction $removeAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $waiting_lists = WaitingList::with(['patient', 'specialty'])
            ->when($request->query('specialty_id'), fn ($q, $specialty_id) => $q->where('specialty_id', $specialty_id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('added_at')
            ->get();

        return response()->json([
            'data' => $waiting_lists->map(fn ($w) => $this->format($w)),
        ], 200);
    }

    public function store(StoreWaitingListRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $data = new WaitingListData(
                patientId: $validated['patient_id'],
                specialtyId: $validated['specialty_id'],
                priority: $validated['priority'] ?? 1,
                reason: $validated['reason'] ?? null,
            );

            $waiting_list = $this->createAction->execute($data);

            return response()->json([
                'message' => 'Interesse registrado com sucesso.',
                'data' => $this->format($waiting_list->load(['patient', 'specialty'])),
            ], 201);
        } catch (PatientAlreadyInWaitingListException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(WaitingList $waitingList): JsonResponse
    {
        return response()->json([
            'data' => $this->format($waitingList->load(['patient', 'specialty'])),
        ], 200);
    }

    public function notify(WaitingList $waitingList): JsonResponse
    {
        try {
            $notified = $this->notifyAction->execute($waitingList->id);

            return response()->json([
                'message' => 'Paciente notificado com sucesso.',
                'data' => [
                    'id' => $notified->id,
                    'status' => $notified->status->value,
                    'notified_at' => $notified->notified_at?->toIso8601String(),
                ],
            ], 200);
        } catch (WaitingListNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(Request $request, WaitingList $waitingList): JsonResponse
    {
        try {
            $this->removeAction->execute(
                $waitingList->id,
                $request->input('reason')
            );

            return response()->json([
                'message' => 'Paciente removido da fila de espera.',
            ], 200);
        } catch (WaitingListNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function format(WaitingList $waiting_list): array
    {
        return [
            'id' => $waiting_list->id,
            'patient' => $waiting_list->patient->name,
            'specialty' => $waiting_list->specialty->name,
            'priority' => $waiting_list->priority,
            'status' => $waiting_list->status->value,
            'reason' => $waiting_list->reason,
            'added_at' => $waiting_list->added_at?->toIso8601String(),
            'notified_at' => $waiting_list->notified_at?->toIso8601String(),
        ];
    }
}
