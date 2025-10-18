<?php

namespace App\Service\Auth;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticatorFactory
{

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RequestStack $requestStack,
    ) {}

    public function __invoke(): SSOAuthenticatorInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->build(
            match (true) {
                $request->query->has('code') => GoogleOAuthService::class,
                default => throw new \RuntimeException('No authenticator found'),
            },
        );
    }

    private function build(string $class): SSOAuthenticatorInterface
    {
        return $this->container->get($class);
    }
}