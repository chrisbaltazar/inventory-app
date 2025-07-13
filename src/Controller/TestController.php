<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Metadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TestController extends AbstractController
{
    private array $msgs = [
        ['text' => 'Test 1...', 'date' => '2024/01/01'],
        ['text' => 'Test 2...', 'date' => '2024/02/01'],
        ['text' => 'Test 3...', 'date' => '2024/03/01'],
    ];

    #[Route('/test/{limit<\d+>?3}', name: 'app_test_index')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(int $limit, EntityManagerInterface $entityManager): Response
    {
        $item = new Item();
        $item->setName('Item 1');
        $item->setRegion('Region 1');
        $item->setSize('S');
        $item->setCreatedAt(new \DateTimeImmutable());
        $item->setUpdatedAt(new \DateTimeImmutable());

        $meta = new Metadata();
        $meta->setName('Meta 1');
        $meta->setValue('Value 1');
        $meta->setItem($item);

        $entityManager->persist($item);
        $entityManager->persist($meta);
        $entityManager->flush();

        return $this->render('test/index.html.twig', [
            'msgs' => $this->msgs,
            'limit' => $limit,
        ]);
    }

    #[Route('/test/show/{id<\d+>}', name: 'app_test_show', priority: 10)]
    public function show(int $id): Response
    {
        return $this->render('test/show.html.twig', [
            'msg' => $this->msgs[$id - 1]['text'] ?? '',
        ]);
    }
}