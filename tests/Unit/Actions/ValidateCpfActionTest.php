<?php

namespace Tests\Unit\Actions;

use App\Domain\Patient\Actions\ValidateCpfAction;
use App\Domain\Patient\Exceptions\InvalidCpfException;
use PHPUnit\Framework\TestCase;

class ValidateCpfActionTest extends TestCase
{
    private ValidateCpfAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateCpfAction();
    }

    public function test_accepts_valid_cpf(): void
    {
        $this->action->execute('11144477735');
        $this->assertTrue(true);
    }

    public function test_rejects_cpf_with_wrong_length(): void
    {
        $this->expectException(InvalidCpfException::class);
        $this->action->execute('123456789');
    }

    public function test_rejects_cpf_with_all_same_digits(): void
    {
        $this->expectException(InvalidCpfException::class);
        $this->action->execute('11111111111');
    }

    public function test_rejects_cpf_with_invalid_check_digit(): void
    {
        $this->expectException(InvalidCpfException::class);
        $this->action->execute('11144477736');
    }

    public function test_accepts_cpf_with_formatting(): void
    {
        // CPF com pontos e hífen
        $this->action->execute('111.444.777-35');
        $this->assertTrue(true);
    }
}