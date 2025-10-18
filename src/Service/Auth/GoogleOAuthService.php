<?php

namespace App\Service\Auth;

use Google\Client;
use Google\Service\Oauth2;
use Symfony\Component\Routing\RouterInterface;

class GoogleOAuthService
{
    private string $accessToken;

    public function __construct(
        private readonly Client $client,
        private readonly RouterInterface $router,
    ) {
        $this->configureClient($router);
    }

    private function configureClient(): void
    {
        $this->client->setRedirectUri($this->getRedirectUrl());
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function getRedirectUrl(): string
    {
        return $this->router->generate(name: 'app_login_auth', referenceType: RouterInterface::ABSOLUTE_URL);
    }

    public function setResponseCode(string $code): void
    {
        $this->accessToken = $this->client->fetchAccessTokenWithAuthCode($code)['access_token'] ??
            throw new \RuntimeException('Failed to fetch access token');

        $this->client->setAccessToken($this->accessToken);
    }

    public function getOAuthUser(): Oauth2\Userinfo
    {
        if (!isset($this->accessToken)) {
            throw new \BadMethodCallException('Access token is not set');
        }

        $auth = new Oauth2($this->client);

        return $auth->userinfo->get();
    }
}
