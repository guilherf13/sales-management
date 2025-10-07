<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar administrador/gestor se não existir
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'perfil' => 'Gestor',
            ]
        );

        // Criar vendedor de teste se não existir
        User::firstOrCreate(
            ['email' => 'vendedor@test.com'],
            [
                'name' => 'Vendedor Teste',
                'password' => Hash::make('password'),
                'perfil' => 'Seller',
            ]
        );
    }
}
