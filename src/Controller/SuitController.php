<?php

namespace App\Controller;

use App\Entity\Suit;
use App\Form\SuitType;
use App\Repository\SuitRepository;
use App\Service\File\SuitFileUploader;
use App\Service\File\SuitFileUploaderResolver;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/suit')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class SuitController extends AbstractController
{
    #[Route('/', name: 'app_suit_index', methods: ['GET'])]
    public function index(SuitRepository $repository): Response
    {
        return $this->render('suit/index.html.twig', [
            'suits' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_suit_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        #[ValueResolver(SuitFileUploaderResolver::class)] SuitFileUploader $uploader,
    ): Response {
        $suit = new Suit();
        $form = $this->createForm(SuitType::class, $suit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePictureUpload($form, $suit, $uploader);
            $entityManager->persist($suit);
            $entityManager->flush();

            $this->addFlash('success', 'Traje creado correctamente');

            return $this->redirectToRoute('app_suit_edit', ['id' => $suit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suit/new.html.twig', [
            'suit' => $suit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suit_show', methods: ['GET'])]
    public function show(Suit $suit): Response
    {
        return $this->render('suit/show.html.twig', [
            'suit' => $suit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_suit_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Suit $suit,
        EntityManagerInterface $entityManager,
        #[ValueResolver(SuitFileUploaderResolver::class)] SuitFileUploader $uploader,
    ): Response {
        $form = $this->createForm(SuitType::class, $suit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePictureUpload($form, $suit, $uploader);
            $entityManager->flush();

            $this->addFlash('success', 'Traje modificado correctamente');

            return $this->redirectToRoute('app_suit_edit', ['id' => $suit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suit/edit.html.twig', [
            'suit' => $suit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/manage', name: 'app_suit_manage', methods: ['GET', 'POST'])]
    public function manage(Request $request, Suit $suit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SuitType::class, $suit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_suit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suit/edit.html.twig', [
            'suit' => $suit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suit_delete', methods: ['POST'])]
    public function delete(Request $request, Suit $suit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $suit->getId(), $request->request->get('_token'))) {
            $suit->setDeletedAt(new DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_suit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/remove-picture', name: 'app_suit_remove_picture', methods: ['PATCH'])]
    public function removePicture(
        Suit $suit,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
    ): JsonResponse {
        $file = $suit->getPicture();
        $filePath = sprintf('%s/public/%s', $params->get('kernel.project_dir'), $file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $suit->setPicture(null);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    private function handlePictureUpload(FormInterface $form, Suit $suit, SuitFileUploader $uploader): void
    {
        $file = $form->get('file')->getData();
        if (!$file) {
            return;
        }

        try {
            $newFilename = $uploader->upload($file);
            $suit->setPicture($newFilename);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to upload picture: ' . $e->getMessage());
        }
    }

}
