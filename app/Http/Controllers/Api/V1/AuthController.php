<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Data\DTO\Auth\LoginDTO;
use App\Data\DTO\Auth\RegisterDTO;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends BaseApiController
{
    public function __construct(private AuthService $auth) {}

    public function register(RegisterRequest $request)
    {
        [$tokenPayload, $user] = $this->auth->register(new RegisterDTO(
            name: $request->name(),
            email: $request->email(),
            password: $request->password(),
        ));

        return $this->actionSuccess('User registered', [
            'token_type'    => $tokenPayload['token_type'] ?? 'Bearer',
            'expires_in'    => $tokenPayload['expires_in'] ?? 0,
            'access_token'  => $tokenPayload['access_token'] ?? null,
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'user'          => new UserResource($user),
        ]);
    }

    public function login(LoginRequest $request)
    {
        [$tokenPayload, $user] = $this->auth->login(new LoginDTO(
            email: $request->email(),
            password: $request->password()
        ));

        return $this->getSuccess([
            'token_type'    => $tokenPayload['token_type'] ?? 'Bearer',
            'expires_in'    => $tokenPayload['expires_in'] ?? 0,
            'access_token'  => $tokenPayload['access_token'] ?? null,
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'user'          => new UserResource($user),
        ], 'Login successful');
    }


    public function me(Request $request)
    {
        return $this->getSuccess(
            data: new UserResource($request->user()),
            message: 'Current user'
        );
    }

    public function logout(Request $request)
    {
        $this->auth->logout($request->user());

        return $this->actionSuccess(
            message: 'Logged out',
            data: null,
            code: 200
        );
    }

    public function logoutAll(Request $request)
    {
        $this->auth->logoutAll(
            $request->user()
        );

        return $this->actionSuccess('Logged out from all devices', null, 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->auth->changePassword(
            user: $request->user(),
            currentPassword: $request->currentPassword(),
            newPassword: $request->newPassword(),
            revokeAllSessions: true // Logout everywhere on password change
        );

        return $this->actionSuccess(
            'Password changed',
            null,
            200
        );
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);
        
        $payload = $this->auth->refresh(
            $request->string('refresh_token')->toString()
        );

        return $this->getSuccess(
            data: [
                'token_type'    => $payload['token_type']   ?? 'Bearer',
                'expires_in'    => $payload['expires_in']   ?? 0,
                'access_token'  => $payload['access_token'] ?? null,
                'refresh_token' => $payload['refresh_token'] ?? null
            ],
            message: 'Token refreshed'
        );
    }
}