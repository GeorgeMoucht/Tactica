<?php

namespace App\Contracts\Auth;

interface TokenIssuer
{
    /**
     * Password grant.
     * @return array{token_type:string,expires_in:int,access_token:string,refresh_token?:string}
     */
    public function issueWithPassword(string $username, string $password, string $score = ''): array;

    /**
     * Refresh grant.
     * @return array{token_type:string,expires_in:int,access_token:string,refresh_token?:string}
     */
    public function issueWithRefreshToken(string $refreshToken, string $scope = ''): array;
}