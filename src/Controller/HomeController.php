<?php

namespace App\Controller;

use App\Service\User\UserHomeDataInterface;
use App\Service\User\UserHomeDataResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(#[ValueResolver(UserHomeDataResolver::class)] ?UserHomeDataInterface $dataService): Response
    {
        return $this->render('home/index.html.twig', [... $dataService?->getData()]);
    }
}
