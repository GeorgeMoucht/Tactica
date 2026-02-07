<?php

namespace App\Services\Auth;

use App\Contracts\Auth\TokenIssuer;
use App\Data\DTO\Auth\LoginDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Data\DTO\Auth\RegisterDTO;
use App\Exceptions\InvalidTokenException;
use App\Exceptions\UnauthorizedException;

class AuthService
{
    public function __construct(
        private UserRepository $users,
        private TokenIssuer $issuer
    ) {}

    /** @return array{0:string, 1:User} */
    public function register(RegisterDTO $dto): array
    {
        // create user with hashed password
        $user = $this->users->create([
            'name'      => $dto->name,
            'email'     => $dto->email,
            'password'  => Hash::make($dto->password),
            'role'      => $dto->role ?? 'teacher',
        ]);

        $tokenPayload = $this->issuer->issueWithPassword(
            $dto->email,
            $dto->password,
            'teacher'
        );

        return [$tokenPayload, $user];
    }

    /** @return array{0:string,1:User} */
    public function login(LoginDTO $dto): array
    {
        $user = $this->users->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new UnauthorizedException('Λάθος Στοιχεία.');
        }


        $scope = $user->role === 'admin' ? 'admin' : 'teacher';
        $tokenPayload = $this->issuer->issueWithPassword(
            $dto->email,
            $dto->password,
            $scope
        );

        return [$tokenPayload, $user];
    }

    public function refresh(string $refreshToken): array
    {
        $payload = $this->issuer->issueWithRefreshToken($refreshToken, '');

        if (!isset($payload['access_token'])) {
            throw new InvalidTokenException('Invalid refresh token');
        }

        return $payload;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword, bool $revokeAllSessions = true): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new UnauthorizedException('Current password is incorrect');
        }

        $user->password = Hash::make($newPassword);
        
        $this->users->save($user);

        if ($revokeAllSessions) {
            $user->tokens()->each(function (\Laravel\Passport\Token $token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });
        }
    }

    public function logout(User $user): void
    {
        /** @var \Laravel\Passport\Token|null $token */
        $token = $user->token();

        // Revoke access token
        if ($token) {
            $token->revoke();
        }

        $token->refreshToken?->revoke();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->each(function (\Laravel\Passport\Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke;
        });
    }
}