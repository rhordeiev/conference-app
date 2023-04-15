<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $user = $request->validated();
        $user['password'] = Hash::make($user['password']);
        $user = new User($user);
        $user->save();
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->validated();
        if ($credentials) {
            $user = User::findUserByEmail($credentials['email']);
            $passwordEqual = Hash::check(
                $credentials['password'],
                $user->password
            );
            if (! $user || ! $passwordEqual || $user->type === 'admin') {
                return response()->json([
                    'errors' => [
                        'email' => ['User not found.'],
                    ],
                ], 404);
            }
            $token = $user->createToken($user['email']);
            $user['token'] = $token->plainTextToken;
            $user['password'] = $credentials['password'];

            return $user;
        }

        return $credentials;
    }

    public function hasToken(Request $request): JsonResponse
    {
        $token = PersonalAccessToken::findToken($request->bearerToken());
        $user = $token->tokenable;
        if (! $user->tokens()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ])->setStatusCode(403);
        }

        return response()->json([
            'message' => 'Authorized.',
        ])->setStatusCode(200);
    }

    public function logout(Request $request)
    {
        $request->user->tokens()->delete();
    }

    public function edit(UserEditRequest $request): JsonResponse
    {
        $userValidated = $request->validated();
        if ($userValidated['previousEmail'] !== $userValidated['email']) {
            $userExists = (bool) User::findUserByEmail($userValidated['email']);
            if ($userExists) {
                return response()->json([
                    'message' => 'Email is already taken.',
                ])->setStatusCode(500);
            }
        }
        User::findUserByEmail($userValidated['previousEmail'])->update(
            $userValidated
        );

        return response()->json([
            'message' => 'User successfully edited.',
        ])->setStatusCode(200);
    }
}
