<?php

namespace App\Service\Loan;

use App\Repository\LoanRepository;

class LoanDataProcessor
{
    public function __construct(private LoanRepository $loanRepository)
    {
    }

    public function __invoke(array $data)
    {

    }
}