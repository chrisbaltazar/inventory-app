<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $repository, EntityManagerInterface $entityManager): Response
    {
        dump($repository->findAll());
        dump($repository->find(1));
        dump($repository->findOneBy(['email' => 'foo@test.com']));
        dump($repository->findBy(['email' => 'foo@test.com']));
        dump($repository->findByEmail('foo@test.com'));

        return $this->render('user/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }

    #[Route('/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/edit', name: 'app_user_edit', methods: ['GET', 'POST'], priority: 2)]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
//        $form = $this->createForm(UserType::class, new User());
        $form = $this->createFormBuilder(new User())
            ->add('name')
            ->add('email')
            ->add('phone')
            ->add('Save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('user/form.html.twig', [
            'form' => $form,
        ]);
    }
}
