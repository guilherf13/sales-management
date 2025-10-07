<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => new UserResource($user)
                ]);
            }

            return response()->json([
                'message' => 'Credenciais inválidas'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            Log::error('Erro ao fazer login: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Não foi possível realizar o login. Por favor, tente novamente mais tarde.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'perfil' => 'Seller',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
                'message' => 'Usuário registrado com sucesso'
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Erro ao registrar usuário: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Não foi possível realizar o cadastro. Por favor, tente novamente mais tarde.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}
