<?php

namespace App\Enum;

enum LoanStatusEnum: string
{
    case OPEN = 'ABIERTO';
    case CLOSED = 'CERRADO';
}
