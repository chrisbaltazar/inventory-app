<?php

namespace App\Service\Loan;

use App\Repository\ItemRepository;
use App\Repository\LoanRepository;

class LoanDataProcessor
{
    public function __construct(
        private LoanRepository $loanRepository,
        private ItemRepository $itemRepository
    ) {
    }

    public function __invoke(array $data)
    {
    }
}