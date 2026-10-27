<?php

namespace App\Controller;

use App\Service\User\UserHomeDataInterface;
use App\Service\User\UserHomeDataResolver;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(
        #[ValueResolver(UserHomeDataResolver::class)] ?UserHomeDataInterface $dataService,
        LoggerInterface $logger,
        Request $request,
    ): Response {
        $logger->error('HOME', [
            'user' => $this->getUser()?->getUserIdentifier(),
            'referer' => $request->headers->get('referer'),
            'secure' => $request->isSecure(),
            'scheme' => $request->getScheme(),
            'x-forwarded-proto' => $request->headers->get('x-forwarded-proto'),
        ]);

        if (!$this->getUser()->isProfileComplete()) {
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('home/index.html.twig', [... $dataService?->getData()]);
    }
}
