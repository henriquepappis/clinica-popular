<?php

namespace Tests\Feature\Doctor;

use App\Domain\Doctor\Models\Doctor;
use App\Domain\Doctor\Actions\CreateDoctorAction;
use App\Domain\Doctor\DataTransferObjects\DoctorData;
use App\Domain\Doctor\Enums\DoctorStatus;
use App\Domain\Doctor\Exceptions\SpecialtyNotFoundException;
use App\Domain\Doctor\Exceptions\DuplicateCrmException;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class CreateDoctorTest extends TestCase
{
    private CreateDoctorAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        Doctor::truncate();
        Specialty::truncate();
        $this->action = app(CreateDoctorAction::class);
    }

    public function test_can_create_doctor(): void
    {
        $specialty = Specialty::factory()->create();

        $data = new DoctorData(
            name: 'Dr. Carlos Silva',
            crm: '123456',
            specialtyIds: [$specialty->id],
            email: 'carlos@example.com',
            phone: '11999999999',
            bio: 'Médico experiente em cardiologia',
        );

        $doctor = $this->action->execute($data);

        $this->assertInstanceOf(Doctor::class, $doctor);
        $this->assertEquals('Dr. Carlos Silva', $doctor->name);
        $this->assertEquals('123456', $doctor->crm);
        $this->assertEquals(DoctorStatus::ACTIVE, $doctor->status);
        $this->assertTrue($doctor->hasSpecialty($specialty->id));

        $this->assertDatabaseHas('doctors', [
            'crm' => '123456',
            'name' => 'Dr. Carlos Silva',
        ]);

        $this->assertDatabaseHas('doctor_specialties', [
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
        ]);
    }

    public function test_can_create_doctor_with_multiple_specialties(): void
    {
        $cardiology = Specialty::factory()->create(['name' => 'Cardiologia ' . uniqid()]);
        $ophthalmology = Specialty::factory()->create(['name' => 'Oftalmologia ' . uniqid()]);

        $data = new DoctorData(
            name: 'Dr. João Multiespecialista',
            crm: '333333',
            specialtyIds: [$cardiology->id, $ophthalmology->id],
        );

        $doctor = $this->action->execute($data);

        $this->assertCount(2, $doctor->specialties);
        $this->assertTrue($doctor->hasSpecialty($cardiology->id));
        $this->assertTrue($doctor->hasSpecialty($ophthalmology->id));
    }

    public function test_cannot_create_doctor_with_invalid_specialty(): void
    {
        $invalidUuid = '00000000-0000-0000-0000-000000000000';

        $data = new DoctorData(
            name: 'Dr. Maria Santos',
            crm: '654321',
            specialtyIds: [$invalidUuid],
            email: 'maria@example.com',
        );

        $this->expectException(SpecialtyNotFoundException::class);
        $this->action->execute($data);
    }

    public function test_cannot_create_doctor_without_specialties(): void
    {
        $data = new DoctorData(
            name: 'Dr. Sem Especialidade',
            crm: '999999',
            specialtyIds: [],
        );

        $this->expectException(SpecialtyNotFoundException::class);
        $this->expectExceptionMessage('Informe ao menos uma especialidade.');
        $this->action->execute($data);
    }

    public function test_cannot_create_duplicate_crm(): void
    {
        $specialty = Specialty::factory()->create();
        Doctor::factory()->create(['crm' => '111111']);

        $data = new DoctorData(
            name: 'Dr. João Silva',
            crm: '111111',
            specialtyIds: [$specialty->id],
        );

        $this->expectException(DuplicateCrmException::class);
        $this->action->execute($data);
    }

    public function test_doctor_status_is_active_by_default(): void
    {
        $specialty = Specialty::factory()->create();

        $data = new DoctorData(
            name: 'Dr. Ana Costa',
            crm: '222222',
            specialtyIds: [$specialty->id],
        );

        $doctor = $this->action->execute($data);

        $this->assertEquals(DoctorStatus::ACTIVE, $doctor->status);
    }

    public function test_doctor_belongs_to_many_specialties(): void
    {
        $specialty = Specialty::factory()->create(['name' => 'Cardiologia ' . uniqid()]);
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();

        $this->assertEquals($specialty->name, $doctor->specialties->first()->name);
    }
}
