<?php

namespace Tests\Feature\Shift;

use App\Domain\Shift\Models\Shift;
use App\Domain\Shift\Actions\CreateShiftAction;
use App\Domain\Shift\DataTransferObjects\ShiftData;
use App\Domain\Shift\Enums\ShiftPeriod;
use App\Domain\Shift\Enums\ShiftStatus;
use Tests\TestCase;

class CreateShiftTest extends TestCase
{
    private CreateShiftAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        Shift::truncate();
        $this->action = app(CreateShiftAction::class);
    }

    public function test_can_create_shift(): void
    {
        $data = new ShiftData(
            name: 'Turno da Manhã',
            period: ShiftPeriod::MORNING->value,
            startTime: '07:00',
            endTime: '12:00',
            maxAppointments: 20
        );

        $shift = $this->action->execute($data);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertEquals('Turno da Manhã', $shift->name);
        $this->assertEquals(ShiftPeriod::MORNING, $shift->period);
        $this->assertEquals(ShiftStatus::ACTIVE, $shift->status);

        $this->assertDatabaseHas('shifts', [
            'name' => 'Turno da Manhã',
            'period' => 'morning',
        ]);
    }

    public function test_shift_status_is_active_by_default(): void
    {
        $data = new ShiftData(
            name: 'Turno da Tarde',
            period: ShiftPeriod::AFTERNOON->value,
            startTime: '12:00',
            endTime: '18:00',
            maxAppointments: 25
        );

        $shift = $this->action->execute($data);

        $this->assertEquals(ShiftStatus::ACTIVE, $shift->status);
    }

    public function test_can_list_active_shifts(): void
    {
        Shift::factory()->create(['status' => ShiftStatus::ACTIVE]);
        Shift::factory()->create(['status' => ShiftStatus::INACTIVE]);

        $shifts = Shift::active()->get();

        $this->assertEquals(1, $shifts->count());
    }

    public function test_can_filter_shifts_by_period(): void
    {
        Shift::factory()->create(['period' => ShiftPeriod::MORNING]);
        Shift::factory()->create(['period' => ShiftPeriod::AFTERNOON]);

        $morning_shifts = Shift::byPeriod(ShiftPeriod::MORNING)->get();

        $this->assertEquals(1, $morning_shifts->count());
    }

    public function test_shift_detects_if_full(): void
    {
        $shift = Shift::factory()->create(['max_appointments' => 1]);
        
        $this->assertFalse($shift->isFull());
    }
}