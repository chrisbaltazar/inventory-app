<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Suit;
use App\Enum\GenderEnum;
use App\Form\SuitType;
use App\Repository\ItemRepository;
use App\Repository\SuitRepository;
use App\Service\File\SuitFileUploader;
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_suit_list');
        }

        return $this->render('suit/index.html.twig', [
            'suits' => $repository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_suit_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SuitFileUploader $uploader,
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

    #[Route('/{id<\d+>}', name: 'app_suit_show', methods: ['GET'])]
    public function show(Suit $suit): Response
    {
        return $this->render('suit/show.html.twig', [
            'suit' => $suit,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_suit_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Suit $suit,
        EntityManagerInterface $entityManager,
        SuitFileUploader $uploader,
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/manage', name: 'app_suit_manage', methods: ['GET', 'POST'])]
    public function manage(
        Request $request,
        Suit $suit,
        ItemRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($request->isMethod('POST')) {
            $items = array_filter($request->get('item', []));
            $items = array_map(fn($itemId) => $repository->find($itemId), array_keys($items));
            $suit->setItems($items);
            $suit->setNote($request->get('note'));
            $entityManager->flush();

            $this->addFlash('success', 'Vestuario asignado correctamente');

            return $this->redirectToRoute('app_suit_manage', ['id' => $suit->getId()], Response::HTTP_SEE_OTHER);
        }

        $items = $repository->findAllByGender(GenderEnum::fromName($suit->getGender()));
        $items = $this->arrangeItemsByRegion($items, $suit->getRegion());

        return $this->render('suit/manage.html.twig', [
            'suit' => $suit,
            'items' => $items,
            'selected' => array_map(fn($item) => $item->getId(), $suit->getItems()->toArray()),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_suit_delete', methods: ['POST'])]
    public function delete(Request $request, Suit $suit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $suit->getId(), $request->request->get('_token'))) {
            $suit->setDeletedAt(new DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Traje borrado correctamente');
        }

        return $this->redirectToRoute('app_suit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/list', name: 'app_suit_list', methods: ['GET'])]
    public function list(SuitRepository $repository): Response
    {
        return $this->render('suit/list.html.twig', [
            'suits' => $repository->findAll(),
        ]);
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

    /**
     * @param Item[] $allItems
     */
    private function arrangeItemsByRegion(array $allItems, string $region): array
    {
        $items = [];
        foreach ($allItems as $item) {
            $items[$item->getRegion()][] = $item;
        }

        $regionItems = $items[$region] ?? [];
        unset($items[$region]);

        return array_merge([$region => $regionItems], $items);
    }
}
