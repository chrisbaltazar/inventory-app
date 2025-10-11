<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserProfileType;
use App\Service\User\UserPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->getUserForm($user);

        if ($form->isSubmitted() && $form->isValid()) {
            $form->handleRequest($request);
            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente');

            return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_profile/index.html.twig', [
            'user' => $this->getUser(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/password', name: 'app_user_password', methods: ['GET', 'POST'])]
    public function password(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordService $userPassword,
    ): Response {
        $this->validateAccess($user);

        $routeBack = $request->get('route_back', 'app_user_profile');
        $form = $this->createForm(UserPasswordType::class, $user, [
            'routeBack' => $routeBack,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setPassword($userPassword($user, $form->get('plainPassword')->getData()));
                $entityManager->flush();
                $this->addFlash('success', 'Password actualizado correctamente');

                return $this->redirectToRoute($routeBack, [], Response::HTTP_SEE_OTHER);
            } catch (Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('user_profile/password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    private function validateAccess(User $user): void
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser->isAdmin() && $currentUser->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }
    }

    private function getUserForm(User $user): FormInterface
    {
        $form = $this->createForm(UserProfileType::class, $user);
        $form->get('name')->setData($user->getName());
        $form->get('email')->setData($user->getEmail());
        $form->get('fullName')->setData($user->getFullName());
        $form->get('officialId')->setData($user->getOfficialId());
        $form->get('phone')->setData($user->getPhone());
        $form->get('birthday')->setData($user->getBirthday());

        return $form;
    }

}
