<?php

namespace App\Infrastructure\Auth;

use App\Contracts\Auth\TokenIssuer;
use Illuminate\Http\Request as LaravelRequest;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PassportTokenIssuer implements TokenIssuer
{
    public function __construct(private AccessTokenController $controller) {}

    public function issueWithPassword(string $username, string $password, string $scope = ''): array
    {
        $laravel = LaravelRequest::create('/oauth/token', 'POST', [
            'grant_type'    => 'password',
            'client_id'     => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'username'      => $username,
            'password'      => $password,
            'scope'         => $scope,
        ]);

        [$psrReq, $psrRes] = $this->toPsr($laravel);
        $psrResponse = $this->controller->issueToken($psrReq, $psrRes);

        return $this->toArray($psrResponse);
    }

    public function issueWithRefreshToken(string $refreshToken, string $scope = ''): array
    {
        $laravel = LaravelRequest::create('/oauth/token', 'POST', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope'         => $scope,
        ]);

        [$psrReq, $psrRes] = $this->toPsr($laravel);
        $psrResponse = $this->controller->issueToken($psrReq, $psrRes);

        return $this->toArray($psrResponse);
    }

    /** @return array{0:ServerRequestInterface,1:PsrResponse} */
    private function toPsr(LaravelRequest $laravel): array
    {
        $psr17 = new Psr17Factory();
        $factory = new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);

        $psrRequest  = $factory->createRequest($laravel);     // ServerRequestInterface
        $psrResponse = $psr17->createResponse();               // ResponseInterface

        return [$psrRequest, $psrResponse];
    }

    /** @return array<string,mixed> */
    private function toArray(PsrResponse|SymfonyResponse $response): array
    {
        if ($response instanceof PsrResponse) {
            $body = (string) $response->getBody();
            $status = $response->getStatusCode();
        } else {
            $body = (string) $response->getContent();
            $status = $response->getStatusCode();
        }

        $data = json_decode($body, true) ?: [];

        return $data;
    }
}
