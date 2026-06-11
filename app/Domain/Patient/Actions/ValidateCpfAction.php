<?php

namespace App\Domain\Patient\Actions;

use App\Domain\Patient\Exceptions\InvalidCpfException;

class ValidateCpfAction
{
    public function execute(string $cpf): void
    {
        // Remover caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verificar se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            throw new InvalidCpfException('CPF deve conter 11 dígitos.');
        }

        // Verificar se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new InvalidCpfException('CPF inválido.');
        }

        // Validar primeiro dígito verificador
        $firstVerifier = $this->calculateVerifier($cpf, 10);
        if ((int)$cpf[9] !== $firstVerifier) {
            throw new InvalidCpfException('CPF inválido.');
        }

        // Validar segundo dígito verificador
        $secondVerifier = $this->calculateVerifier($cpf, 11);
        if ((int)$cpf[10] !== $secondVerifier) {
            throw new InvalidCpfException('CPF inválido.');
        }
    }

    private function calculateVerifier(string $cpf, int $length): int
    {
        $sum = 0;
        $multiplier = $length;

        for ($i = 0; $i < $length - 1; $i++) {
            $sum += (int)$cpf[$i] * $multiplier;
            $multiplier--;
        }

        $remainder = $sum % 11;
        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}