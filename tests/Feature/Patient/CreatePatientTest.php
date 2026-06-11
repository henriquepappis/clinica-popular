<?php

namespace Tests\Feature\Patient;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Actions\CreatePatientAction;
use App\Domain\Patient\DataTransferObjects\PatientData;
use App\Domain\Patient\Enums\PatientStatus;
use App\Domain\Patient\Exceptions\InvalidCpfException;
use App\Domain\Patient\Exceptions\DuplicatePatientException;
use Carbon\Carbon;
use Tests\TestCase;

class CreatePatientTest extends TestCase
{
    private CreatePatientAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreatePatientAction::class);
    }

    public function test_can_create_patient_with_valid_data(): void
    {
        $factory = Patient::factory();
        $cpf = $factory->make()->cpf;
        $name = 'João Silva';

        $data = new PatientData(
            name: $name,
            cpf: $cpf,
            birthDate: Carbon::parse('1990-01-01'),
            phone: '11999999999',
        );

        $patient = $this->action->execute($data);

        $this->assertInstanceOf(Patient::class, $patient);
        $this->assertEquals($name, $patient->name);
        $this->assertEquals($cpf, $patient->cpf);
        $this->assertEquals(PatientStatus::ACTIVE, $patient->status);

        $this->assertDatabaseHas('patients', [
            'cpf' => $cpf,
            'name' => $name,
        ]);
    }

    public function test_cannot_create_patient_with_invalid_cpf(): void
    {
        $data = new PatientData(
            name: 'João Silva',
            cpf: '00000000000',
            birthDate: Carbon::parse('1990-01-01'),
            phone: '11999999999',
        );

        $this->expectException(InvalidCpfException::class);
        $this->action->execute($data);
    }

    public function test_cannot_create_duplicate_patient(): void
    {
        $cpf = Patient::factory()->create()->cpf;

        $data = new PatientData(
            name: 'Outro Nome',
            cpf: $cpf,
            birthDate: Carbon::parse('1990-01-01'),
            phone: '11999999999',
        );

        $this->expectException(DuplicatePatientException::class);
        $this->action->execute($data);
    }

    public function test_patient_status_is_active_by_default(): void
    {
        $factory = Patient::factory();
        $cpf = $factory->make()->cpf;

        $data = new PatientData(
            name: 'Maria Santos',
            cpf: $cpf,
            birthDate: Carbon::parse('1990-01-01'),
            phone: '11999999999',
        );

        $patient = $this->action->execute($data);

        $this->assertEquals(PatientStatus::ACTIVE, $patient->status);
    }

    public function test_dispatches_patient_registered_event(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $factory = Patient::factory();
        $cpf = $factory->make()->cpf;

        $data = new PatientData(
            name: 'Pedro Costa',
            cpf: $cpf,
            birthDate: Carbon::parse('1990-01-01'),
            phone: '11999999999',
        );

        $patient = $this->action->execute($data);

        \Illuminate\Support\Facades\Event::assertDispatched(
            \App\Domain\Patient\Events\PatientRegistered::class,
            fn ($event) => $event->patient->id === $patient->id
        );
    }
}