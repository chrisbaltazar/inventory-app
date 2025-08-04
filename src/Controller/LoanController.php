<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Loan;
use App\Entity\User;
use App\Enum\RegionEnum;
use App\Form\LoanType;
use App\Repository\LoanRepository;
use App\Service\Inventory\InventoryDataService;
use App\Service\Loan\LoanDataProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/loan')]
#[IsGranted("ROLE_ADMIN")]
class LoanController extends AbstractController
{
    #[Route('/new/{event?}/{user?}', name: 'app_loan_new', methods: ['GET'])]
    public function new(
        Request $request,
        ?Event $event,
        ?User $user,
        InventoryDataService $inventoryDataService,
    ): Response {
        $region = $request->get('region');
        $form = $this->createForm(LoanType::class, new Loan(), [
            'event' => $event,
            'user' => $user,
            'region' => $region,
        ]);

        $form->handleRequest($request);

        $inventory = [];
        if ($region) {
            $region = RegionEnum::from($region);
            $inventory = $inventoryDataService($region);
        }

        return $this->render('loan/new.html.twig', [
            'form' => $form,
            'inventory' => $inventory,
        ]);
    }

    #[Route('/store', name: 'app_loan_store', methods: ['POST'])]
    public function store(Request $request, LoanDataProcessor $loanDataProcessor): Response
    {
        try {
            $loanDataProcessor($request->getPayload()->all('loan'));

            $this->addFlash('success', 'Asignación correcta.');

            return $this->json('OK');
        } catch (\UnexpectedValueException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $t) {
            return $this->json(['message' => $t->getMessage(), 'error' => $t->getTraceAsString()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/user/{id?}', name: 'app_loan_user', methods: ['GET'])]
   public function showUser(?User $user = null): Response
   {
        return $this->render('loan/user.html.twig', [
            'user' => $user,
        ]);
   }

   public function showItem(): Response
   {

   }
}
