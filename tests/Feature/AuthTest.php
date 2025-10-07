<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Autenticação', function () {
    
    test('usuário pode fazer login com credenciais válidas', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'perfil',
                ],
            ])
            ->assertJson([
                'token_type' => 'Bearer',
                'user' => [
                    'email' => 'test@example.com',
                ],
            ]);
    });

    test('usuário não pode fazer login com senha incorreta', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Credenciais inválidas',
            ]);
    });

    test('usuário não pode fazer login com email inexistente', function () {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Credenciais inválidas',
            ]);
    });

    test('login requer email válido', function () {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('login requer senha', function () {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('usuário pode se registrar com dados válidos', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'perfil',
                ],
                'message',
            ])
            ->assertJson([
                'token_type' => 'Bearer',
                'user' => [
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                    'perfil' => 'Seller',
                ],
                'message' => 'Usuário registrado com sucesso',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'perfil' => 'Seller',
        ]);
    });

    test('registro requer nome', function () {
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('registro requer email válido', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('registro requer email único', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('registro requer confirmação de senha', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('registro requer senhas correspondentes', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('usuário autenticado pode fazer logout', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Logout realizado com sucesso',
            ]);

        // Verifica que o token foi excluído
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    });

    test('usuário não autenticado não pode fazer logout', function () {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    });

    test('usuário autenticado pode obter seus dados', function () {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'perfil' => 'Gestor',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'perfil' => 'Gestor',
            ]);
    });
});

