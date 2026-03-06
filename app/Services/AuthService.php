<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        // Hash the password (like Python's passlib/bcrypt)
        $data['password'] = Hash::make($data['password']);
        
        // Default role if not provided
        $data['role'] = $data['role'] ?? 'developer';

        $user = User::create($data);
        $token = $user->createToken('api_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api_token')->plainTextToken
        ];
    }

    public function logout(User $user): void
    {
        // This targets the specific token used to authenticate this request
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }
}