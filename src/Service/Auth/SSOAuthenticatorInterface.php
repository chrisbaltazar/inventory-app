<?php

namespace App\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

interface SSOAuthenticatorInterface
{

    public function authenticate(Request $request): UserInterface;

}