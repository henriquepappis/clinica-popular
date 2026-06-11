<?php

namespace Tests\Feature\Specialty;

use App\Domain\Specialty\Models\Specialty;
use App\Domain\Specialty\Actions\CreateSpecialtyAction;
use App\Domain\Specialty\DataTransferObjects\SpecialtyData;
use App\Domain\Specialty\Enums\SpecialtyStatus;
use Tests\TestCase;

class CreateSpecialtyTest extends TestCase
{
    private CreateSpecialtyAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        Specialty::truncate();  // Limpar antes de cada teste
        $this->action = app(CreateSpecialtyAction::class);
    }

    public function test_can_create_specialty(): void
    {
        $data = new SpecialtyData(
            name: 'Cardiologia ' . uniqid(),
            description: 'Especialidade em doenças do coração',
        );

        $specialty = $this->action->execute($data);

        $this->assertInstanceOf(Specialty::class, $specialty);
        $this->assertEquals(SpecialtyStatus::ACTIVE, $specialty->status);

        $this->assertDatabaseHas('specialties', [
            'name' => $data->name,
        ]);
    }

    public function test_specialty_status_is_active_by_default(): void
    {
        $data = new SpecialtyData(
            name: 'Pediatria ' . uniqid(),
            description: 'Especialidade em crianças',
        );

        $specialty = $this->action->execute($data);

        $this->assertEquals(SpecialtyStatus::ACTIVE, $specialty->status);
    }

    public function test_can_list_active_specialties(): void
    {
        Specialty::factory()->create(['status' => SpecialtyStatus::ACTIVE]);
        Specialty::factory()->create(['status' => SpecialtyStatus::INACTIVE]);

        $specialties = Specialty::active()->get();

        $this->assertEquals(1, $specialties->count());
    }
}