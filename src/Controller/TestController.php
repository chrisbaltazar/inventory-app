<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    private array $msgs = [
        ['text' => 'Test 1...', 'date' => '2024/01/01'],
        ['text' => 'Test 2...', 'date' => '2024/02/01'],
        ['text' => 'Test 3...', 'date' => '2024/03/01'],
    ];

    #[Route('/test/{limit<\d+>?3}', name: 'app_test_index')]
    public function index(int $limit): Response
    {
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