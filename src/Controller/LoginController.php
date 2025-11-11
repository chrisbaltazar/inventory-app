<?php

namespace App\Controller;

use App\Form\RecoverPasswordType;
use App\Repository\UserRepository;
use App\Service\Auth\GoogleOAuthService;
use App\Service\Auth\SSOAuthenticatorInterface;
use App\Service\User\UserAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginController extends AbstractController
{
    const USER_ACCESS_ID = '_sdm_user_access_id';

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
        Request $request,
        SSOAuthenticatorInterface $auth,
        Security $security,
    ): Response {
        try {
            $user = $auth->authenticate($request);

            return $security->login($user);
        } catch (\RuntimeException $e) {
            return $this->redirectWithAuthError($request, $e);
        } catch (\Throwable $t) {
            throw $this->createAccessDeniedException($t->getMessage(), $t);
        }
    }

    #[Route('/recover-access', name: 'app_login_recover', methods: ['GET', 'POST'])]
    public function recoverAccess(
        Request $request,
        UserRepository $userRepository,
        UserAccessService $userAccess,
    ): Response {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home_index');
        }

        $form = $this->createForm(RecoverPasswordType::class);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $search = ['email' => $form->get('email')->getData()];
                $user = $userAccess->make($search);
                $request->getSession()->set(self::USER_ACCESS_ID, $user->getId());

                return $this->redirectToRoute('app_login_code');
            }
        } catch (\Exception $e) {
            return $this->redirectWithAuthError($request, $e);
        }

        return $this->render('login/recover.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/login/code', name: 'app_login_code', methods: ['GET', 'POST'])]
    public function userCode(Request $request, UserRepository $userRepository): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home_index');
        }

        $userSessionId = $request->getSession()->get(self::USER_ACCESS_ID);
        $user = $userRepository->find($userSessionId);
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $expiration = $user->getCodeExpiration();
        if (!$expiration || $expiration < new \DateTime('now')) {
            return $this->redirectWithAuthError($request, new \RuntimeException('Access code expired'));
        }

        $number = substr($user->getPhone(), -3);
        $time = $expiration->getTimestamp() - time();

        return $this->render('login/code.html.twig', [
            'time' => $time,
            'number' => $number,
        ]);
    }

    private function redirectWithAuthError(Request $request, \Exception $e): Response
    {
        $session = $request->getSession();
        $session->set(
            SecurityRequestAttributes::AUTHENTICATION_ERROR,
            new AuthenticationException($e->getMessage(), 0, $e),
        );

        return $this->redirectToRoute('app_login');
    }
}
