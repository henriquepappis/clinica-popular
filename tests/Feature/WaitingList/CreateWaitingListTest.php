<?php

namespace Tests\Feature\WaitingList;

use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\WaitingList\Enums\WaitingListStatus;
use App\Domain\WaitingList\Actions\CreateWaitingListAction;
use App\Domain\WaitingList\Actions\NotifyWaitingListAction;
use App\Domain\WaitingList\Actions\RemoveFromWaitingListAction;
use App\Domain\WaitingList\DataTransferObjects\WaitingListData;
use App\Domain\Patient\Models\Patient;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class CreateWaitingListTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        WaitingList::truncate();
        Patient::truncate();
        Specialty::truncate();
    }

    public function test_can_add_patient_to_waiting_list(): void
    {
        $patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $action = app(CreateWaitingListAction::class);

        $data = new WaitingListData(
            patientId: $patient->id,
            specialtyId: $specialty->id,
            priority: 2,
            reason: 'Paciente em espera por consulta'
        );

        $waitingList = $action->execute($data);

        $this->assertInstanceOf(WaitingList::class, $waitingList);
        $this->assertEquals(WaitingListStatus::WAITING, $waitingList->status);
        $this->assertEquals(2, $waitingList->priority);

        $this->assertDatabaseHas('waiting_lists', [
            'patient_id' => $patient->id,
            'specialty_id' => $specialty->id,
        ]);
    }

    public function test_cannot_add_patient_twice_for_same_specialty(): void
    {
        $patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $action = app(CreateWaitingListAction::class);

        $data = new WaitingListData(
            patientId: $patient->id,
            specialtyId: $specialty->id,
            priority: 1,
        );

        $action->execute($data);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Paciente já está na fila');
        $action->execute($data);
    }

    public function test_can_notify_patient(): void
    {
        $waitingList = WaitingList::factory()->create();
        $action = app(NotifyWaitingListAction::class);

        $notified = $action->execute($waitingList->id);

        $this->assertEquals(WaitingListStatus::NOTIFIED, $notified->status);
        $this->assertNotNull($notified->notified_at);

        $this->assertDatabaseHas('waiting_lists', [
            'id' => $waitingList->id,
            'status' => 'notified',
        ]);
    }

    public function test_can_remove_from_waiting_list(): void
    {
        $waitingList = WaitingList::factory()->create();
        $action = app(RemoveFromWaitingListAction::class);

        $cancelled = $action->execute($waitingList->id, 'Paciente não compareceu');

        $this->assertEquals(WaitingListStatus::CANCELLED, $cancelled->status);

        $this->assertDatabaseHas('waiting_lists', [
            'id' => $waitingList->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_list_waiting_patients_by_priority(): void
    {
        $specialty = Specialty::factory()->create();

        WaitingList::factory()->create([
            'specialty_id' => $specialty->id,
            'priority' => 1,
        ]);

        WaitingList::factory()->create([
            'specialty_id' => $specialty->id,
            'priority' => 3,
        ]);

        $highPriority = WaitingList::bySpecialty($specialty->id)
            ->byPriority(3)
            ->get();

        $this->assertEquals(1, $highPriority->count());
    }
}
