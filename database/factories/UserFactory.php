<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'cpf_cnpj' => $this->faker->randomElement([$this->generateCpf(), $this->generateCnpj()]),
            'password' => Hash::make('password'), // Senha padrão para testes
            'tipo_usuario' => $this->faker->randomElement(['cliente', 'lojista']),
            'saldo' => $this->faker->randomFloat(2, 0, 10000),
            'remember_token' => $this->faker->optional()->sha256(),
        ];
    }

    private function generateCpf()
    {
        $cpf = [];
        for ($i = 0; $i < 9; $i++) {
            $cpf[] = mt_rand(0, 9);
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            $cpf[$t] = $d;
        }

        return implode('', $cpf);
    }

    private function generateCnpj()
    {
        $cnpj = [];
        for ($i = 0; $i < 12; $i++) {
            $cnpj[] = mt_rand(0, 9);
        }

        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($t = 0; $t < 2; $t++) {
            $d = 0;
            $start = $t === 0 ? 0 : 1;
            for ($i = 0; $i < count($weights); $i++) {
                $d += $cnpj[$i] * $weights[$i];
            }
            $d = ((10 * $d) % 11) % 10;
            $cnpj[] = $d;
        }

        return implode('', $cnpj);
    }
}
