<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Service\User\UserPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_user_profile')]
    public function index(): Response
    {
        return $this->render('user_profile/index.html.twig', [
            'controller_name' => 'UserProfileController',
        ]);
    }

    #[Route('/{id}/password', name: 'app_user_password', methods: ['GET', 'POST'])]
    public function password(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordService $userPassword
    ): Response {
        $form = $this->createForm(UserPasswordType::class, $user, [
            'routeBack' => 'app_user_profile',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setPassword($userPassword($user, $form->get('plainPassword')->getData()));
                $entityManager->flush();
                $this->addFlash('success', 'Password actualizado correctamente');

                return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('user_profile/password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
