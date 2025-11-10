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
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

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
                $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);
                if (!$user) {
                    throw new \UnexpectedValueException('User not found');
                }

                $accessToken = $userAccess->getAccessToken($user);

                return $this->redirectToRoute('app_login_code', ['t' => $accessToken]);
            }
        } catch (\Exception $e) {
            return $this->redirectWithAuthError($request, $e);
        }

        return $this->render('login/recover.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/login/code', name: 'app_login_code', methods: ['GET', 'POST'])]
    public function userCode(#[MapQueryParameter] string $t, UserAccessService $userAccess): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home_index');
        }

        try {
            $data = $userAccess->getAccessData($t);
        } catch (\Exception $e) {
            throw $this->createAccessDeniedException($e->getMessage(), $e);
        }

        return $this->render('login/code.html.twig', [
            'time' => $data->expiration,
            'number' => $data->userNumber,
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
