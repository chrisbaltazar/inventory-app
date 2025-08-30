<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Loan;
use App\Entity\User;
use App\Enum\LoanStatusEnum;
use App\Enum\RegionEnum;
use App\Form\LoanReturnType;
use App\Form\LoanType;
use App\Repository\EventRepository;
use App\Repository\ItemRepository;
use App\Repository\LoanRepository;
use App\Repository\UserRepository;
use App\Service\Inventory\InventoryDataService;
use App\Service\Loan\LoanDataService;
use App\Service\Loan\LoanProcessor;
use App\Service\Loan\LoanTransferService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    public function store(Request $request, LoanProcessor $loanDataProcessor): Response
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
    public function showUser(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        LoanDataService $loanDataService,
        ?User $user = null
    ): Response {
        $openLoans = [];
        $closedLoans = [];
        $futureEvents = [];
        if ($user) {
            $allLoans = $loanDataService->getUserLoansByEvent($user);
            $openLoans = array_filter($allLoans, function (array $loan) {
                return $loan['data']['isOpen'];
            });
            $closedLoans = array_filter($allLoans, function (array $loan) {
                return !$loan['data']['isOpen'];
            });
            $futureEvents = $eventRepository->findAllFuture();
        }

        $loan = new Loan();
        $loan->setEndDate(new \DateTimeImmutable());
        $returnForm = $this->createForm(LoanReturnType::class, $loan);

        return $this->render('loan/user.html.twig', [
            'user' => $user,
            'users' => $userRepository->findAll(),
            'form' => $returnForm,
            'futureEvents' => $futureEvents,
            'openLoans' => $openLoans,
            'closedLoans' => $closedLoans,
            'closedStatus' => LoanStatusEnum::CLOSED->value,
        ]);
    }

    #[Route('/item/{item?}/{invent?}', name: 'app_loan_item', methods: ['GET'])]
    public function showItem(
        ItemRepository $itemRepository,
        LoanRepository $loanRepository,
        ?Item $item = null,
        ?Inventory $invent = null
    ): Response {
        return $this->render('loan/item.html.twig', [
            'items' => $itemRepository->findAll(),
            'item' => $item,
            'inventory' => $item?->getInventory() ?? [],
            'info' => $invent?->getId(),
            'loans' => $item ? $loanRepository->findAllByItem($item, $invent) : [],
        ]);
    }

    #[Route('/update', name: 'app_loan_update', methods: ['POST'])]
    public function update(
        Request $request,
        LoanRepository $loanRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $id = $request->get('id');
        $loan = $loanRepository->find($id) ?? throw new NotFoundHttpException();
        try {
            $form = $this->createForm(LoanReturnType::class, $loan, [
                'csrf_protection' => false,
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var \DateTime $endDate */
                $endDate = $form->get('endDate')->getData();
                $startDate = $loan->getStartDate();
                // Considered a mistake and remove it
                if ($endDate->format('Y-m-d') === $startDate->format('Y-m-d')) {
                    $entityManager->remove($loan);
                }
                $entityManager->flush();

                return $this->json('OK');
            }

            return $this->json(['errors' => $form->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $t) {
            return $this->json(['message' => $t->getMessage(), 'error' => $t->getTraceAsString()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/reopen', name: 'app_loan_reopen', methods: ['GET'])]
    public function reOpenLoan(Loan $loan, EntityManagerInterface $entityManager): Response
    {
        $loan->setEndDate(null);
        $loan->setStatus(LoanStatusEnum::OPEN->value);
        $entityManager->flush();

        return $this->json('OK');
    }

    #[Route('/transfer', name: 'app_loan_transfer', methods: ['GET'])]
    public function transferLoan(
        #[MapQueryParameter] int $source,
        #[MapQueryParameter] int $target,
        #[MapQueryParameter] int $user,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        LoanTransferService $loanTransfer
    ): Response {
        try {
            $user = $userRepository->find($user) ?? throw new NotFoundHttpException();
            $sourceEvent = $eventRepository->find($source) ?? throw new NotFoundHttpException();
            $targetEvent = $eventRepository->find($target) ?? throw new NotFoundHttpException();

            $loanTransfer($user, $sourceEvent, $targetEvent);

            $this->addFlash('success', 'Transferencia completa');
        } catch (\UnexpectedValueException $ex) {
            $this->addFlash('error', $ex->getMessage());
        }

        return $this->redirectToRoute('app_loan_user', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }
}
