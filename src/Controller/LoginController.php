<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Auth\GoogleOAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $utils, GoogleOAuthService $googleAuth): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home_index');
        }

        $error = $utils->getLastAuthenticationError();

        return $this->render('login/index.html.twig', [
            'error' => $error,
            'googleAuthUrl' => $googleAuth->getAuthUrl(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Handled by symfony
    }

    #[Route('/auth', name: 'app_login_auth', methods: ['GET'])]
    public function auth(
        #[MapQueryParameter] string $code,
        GoogleOAuthService $googleAuth,
        UserRepository $repository,
        Security $security,
    ): Response {
        try {
            if (!$code) {
                throw new \UnexpectedValueException('No code provided');
            }
            $googleAuth->setResponseCode($code);
            $authUser = $googleAuth->getOAuthUser();
            $user = $repository->findOneBy(['email' => $authUser->getEmail()]);
            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            return $security->login($user);
        } catch (\Throwable $t) {
            throw $this->createAccessDeniedException();
        }
    }
}
