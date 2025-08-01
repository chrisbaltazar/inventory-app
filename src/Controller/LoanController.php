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
    #[Route('/', name: 'app_loan_index', methods: ['GET'])]
    public function index(LoanRepository $loanRepository): Response
    {
        return $this->render('loan/index.html.twig', [
            'loans' => $loanRepository->findAll(),
        ]);
    }

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

            return $this->json('OK');
        } catch (\UnexpectedValueException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $t) {
            throw $t;
//            return $this->json(['error' => $t->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_loan_show', methods: ['GET'])]
    public function show(Loan $loan): Response
    {
        return $this->render('loan/show.html.twig', [
            'loan' => $loan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_loan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Loan $loan, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loan/edit.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_loan_delete', methods: ['POST'])]
    public function delete(Request $request, Loan $loan, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $loan->getId(), $request->request->get('_token'))) {
            $entityManager->remove($loan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
    }
}
