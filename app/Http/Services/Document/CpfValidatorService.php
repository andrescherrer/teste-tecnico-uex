<?php

namespace App\Http\Services\Document;

class CpfValidatorService
{
    public function execute(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;

            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }

            $resto = ($soma * 10) % 11;
            $resto = ($resto === 10) ? 0 : $resto;

            if ($cpf[$t] != $resto) {
                return false;
            }
        }

        return true;
    }
}
