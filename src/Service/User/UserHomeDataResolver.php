<?php

namespace App\Service\User;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UserHomeDataResolver implements ValueResolverInterface
{
    public function __construct(
        private Security $security,
        private UserHomeDataService $userHomeDataService,
        private AdminHomeDataService $adminHomeDataService,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserHomeDataInterface::class) {
            return [];
        }

        return match (true) {
            $this->security->isGranted('ROLE_ADMIN') => [$this->adminHomeDataService],
            $this->security->isGranted('ROLE_USER') => [$this->userHomeDataService],
        };
    }
}