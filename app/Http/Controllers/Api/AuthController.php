<?php

namespace App\Http\Controllers\Api;

use App\Domain\Auth\Models\User;
use App\Http\Controllers\Controller;
use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\RegisterAction;
use App\Domain\Auth\DataTransferObjects\LoginData;
use App\Domain\Auth\DataTransferObjects\RegisterData;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Exceptions\UserAlreadyExistsException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private LoginAction $loginAction,
        private RegisterAction $registerAction,
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|string|in:admin,doctor,receptionist,patient',
                'phone' => 'nullable|string',
            ]);

            $data = new RegisterData(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                role: $validated['role'],
                phone: $validated['phone'] ?? null,
            );

            $user = $this->registerAction->execute($data);

            return response()->json([
                'message' => 'Usuário registrado com sucesso.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->label(),
                ],
            ], 201);
        } catch (UserAlreadyExistsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $data = new LoginData(
                email: $validated['email'],
                password: $validated['password'],
            );

            $result = $this->loginAction->execute($data);

            return response()->json([
                'message' => 'Login realizado com sucesso.',
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => $result['user']->role->label(),
                ],
                'token' => $result['token'],
            ], 200);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->label(),
                'phone' => $user->phone,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }
}
