<?php

namespace Tests\Feature\Price;

use App\Domain\Price\Models\Price;
use App\Domain\Price\Enums\PriceType;
use App\Domain\Price\Actions\CreatePriceAction;
use App\Domain\Price\Actions\UpdatePriceAction;
use App\Domain\Price\Actions\DeletePriceAction;
use App\Domain\Price\Actions\GetPriceForAppointmentAction;
use App\Domain\Price\DataTransferObjects\PriceData;
use App\Domain\Price\Exceptions\PriceNotFoundException;
use App\Domain\Price\Exceptions\InvalidPriceException;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class PriceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Price::truncate();
        Doctor::truncate();
        Specialty::truncate();
    }

    public function test_can_create_price_for_specialty(): void
    {
        $specialty = Specialty::factory()->create();
        $action = app(CreatePriceAction::class);

        $price = $action->execute(new PriceData(
            value: 150.00,
            specialtyId: $specialty->id,
        ));

        $this->assertInstanceOf(Price::class, $price);
        $this->assertEquals(PriceType::SPECIALTY, $price->type());

        $this->assertDatabaseHas('prices', [
            'specialty_id' => $specialty->id,
            'value' => 150.00,
        ]);
    }

    public function test_can_create_price_for_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $action = app(CreatePriceAction::class);

        $price = $action->execute(new PriceData(
            value: 200.00,
            doctorId: $doctor->id,
        ));

        $this->assertEquals(PriceType::DOCTOR, $price->type());

        $this->assertDatabaseHas('prices', [
            'doctor_id' => $doctor->id,
            'value' => 200.00,
        ]);
    }

    public function test_can_create_price_for_duration(): void
    {
        $action = app(CreatePriceAction::class);

        $price = $action->execute(new PriceData(
            value: 80.00,
            durationMinutes: 30,
        ));

        $this->assertEquals(PriceType::DURATION, $price->type());

        $this->assertDatabaseHas('prices', [
            'duration_minutes' => 30,
            'value' => 80.00,
        ]);
    }

    public function test_cannot_create_price_with_invalid_value(): void
    {
        $specialty = Specialty::factory()->create();
        $action = app(CreatePriceAction::class);

        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('maior que zero');

        $action->execute(new PriceData(
            value: 0,
            specialtyId: $specialty->id,
        ));
    }

    public function test_cannot_create_price_without_target(): void
    {
        $action = app(CreatePriceAction::class);

        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Informe médico, especialidade ou duração');

        $action->execute(new PriceData(value: 100.00));
    }

    public function test_cannot_create_duplicate_price_config(): void
    {
        $specialty = Specialty::factory()->create();
        $action = app(CreatePriceAction::class);

        $data = new PriceData(value: 150.00, specialtyId: $specialty->id);
        $action->execute($data);

        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Já existe um preço configurado');
        $action->execute($data);
    }

    public function test_can_update_price(): void
    {
        $price = Price::factory()->create(['value' => 100.00]);
        $action = app(UpdatePriceAction::class);

        $updated = $action->execute($price->id, 180.00);

        $this->assertEquals(180.00, (float) $updated->value);

        $this->assertDatabaseHas('prices', [
            'id' => $price->id,
            'value' => 180.00,
        ]);
    }

    public function test_update_throws_when_price_not_found(): void
    {
        $action = app(UpdatePriceAction::class);

        $this->expectException(PriceNotFoundException::class);

        $action->execute('00000000-0000-0000-0000-000000000000', 150.00);
    }

    public function test_can_delete_price(): void
    {
        $price = Price::factory()->create();
        $action = app(DeletePriceAction::class);

        $action->execute($price->id);

        $this->assertDatabaseMissing('prices', [
            'id' => $price->id,
        ]);
    }

    public function test_doctor_price_overrides_specialty_price(): void
    {
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();

        Price::factory()->create([
            'doctor_id' => null,
            'specialty_id' => $specialty->id,
            'value' => 150.00,
        ]);

        Price::factory()->create([
            'doctor_id' => $doctor->id,
            'specialty_id' => null,
            'value' => 200.00,
        ]);

        $value = app(GetPriceForAppointmentAction::class)->execute($doctor->id);

        $this->assertEquals(200.00, $value);
    }

    public function test_uses_specialty_price_when_no_doctor_price(): void
    {
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();

        Price::factory()->create([
            'doctor_id' => null,
            'specialty_id' => $specialty->id,
            'value' => 150.00,
        ]);

        $value = app(GetPriceForAppointmentAction::class)->execute($doctor->id);

        $this->assertEquals(150.00, $value);
    }

    public function test_uses_informed_specialty_when_doctor_has_many(): void
    {
        $cardiology = Specialty::factory()->create(['name' => 'Cardiologia ' . uniqid()]);
        $ophthalmology = Specialty::factory()->create(['name' => 'Oftalmologia ' . uniqid()]);
        $doctor = Doctor::factory()->withSpecialties($cardiology, $ophthalmology)->create();

        Price::factory()->create([
            'doctor_id' => null,
            'specialty_id' => $cardiology->id,
            'value' => 150.00,
        ]);

        Price::factory()->create([
            'doctor_id' => null,
            'specialty_id' => $ophthalmology->id,
            'value' => 120.00,
        ]);

        $value = app(GetPriceForAppointmentAction::class)
            ->execute($doctor->id, specialtyId: $ophthalmology->id);

        $this->assertEquals(120.00, $value);
    }

    public function test_falls_back_to_default_clinic_price(): void
    {
        config(['clinica.default_appointment_price' => 100.00]);
        $doctor = Doctor::factory()->create();

        $value = app(GetPriceForAppointmentAction::class)->execute($doctor->id);

        $this->assertEquals(100.00, $value);
    }

    public function test_prefers_duration_specific_price(): void
    {
        $doctor = Doctor::factory()->create();

        Price::factory()->create([
            'doctor_id' => $doctor->id,
            'specialty_id' => null,
            'duration_minutes' => null,
            'value' => 200.00,
        ]);

        Price::factory()->create([
            'doctor_id' => $doctor->id,
            'specialty_id' => null,
            'duration_minutes' => 60,
            'value' => 350.00,
        ]);

        $value = app(GetPriceForAppointmentAction::class)
            ->execute($doctor->id, durationMinutes: 60);

        $this->assertEquals(350.00, $value);
    }
}
