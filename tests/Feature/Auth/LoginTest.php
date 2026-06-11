<?php

namespace Tests\Feature\Auth;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\RegisterAction;
use App\Domain\Auth\DataTransferObjects\LoginData;
use App\Domain\Auth\DataTransferObjects\RegisterData;
use App\Domain\Auth\Enums\UserRole;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::truncate();
    }

    public function test_user_can_register(): void
    {
        $registerAction = app(RegisterAction::class);

        $data = new RegisterData(
            name: 'João Silva',
            email: 'joao@example.com',
            password: 'password123',
            role: UserRole::PATIENT->value,
            phone: '11999999999'
        );

        $user = $registerAction->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('joao@example.com', $user->email);
        $this->assertEquals(UserRole::PATIENT, $user->role);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'teste@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginAction = app(LoginAction::class);

        $data = new LoginData(
            email: 'teste@example.com',
            password: 'password123'
        );

        $result = $loginAction->execute($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'teste@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginAction = app(LoginAction::class);

        $data = new LoginData(
            email: 'teste@example.com',
            password: 'wrongpassword'
        );

        $this->expectException(InvalidCredentialsException::class);
        $loginAction->execute($data);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginAction = app(LoginAction::class);

        $data = new LoginData(
            email: 'inactive@example.com',
            password: 'password123'
        );

        $this->expectException(InvalidCredentialsException::class);
        $loginAction->execute($data);
    }

    public function test_user_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $doctor = User::factory()->doctor()->create();
        $receptionist = User::factory()->receptionist()->create();
        $patient = User::factory()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($doctor->isDoctor());
        $this->assertTrue($receptionist->isReceptionist());
        $this->assertTrue($patient->isPatient());
    }
}
