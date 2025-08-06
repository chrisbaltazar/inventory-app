<?php

namespace App\Enum;

enum LoanStatusEnum: string
{
    use WithEnumValues;

    case OPEN = 'ABIERTO';
    case CLOSED = 'CORRECTO';
    case DAMAGED = 'DAÑADO';
    case LOST = 'PERDIDO';

    public static function getClosureValues(): array
    {
        return [self::CLOSED, self::DAMAGED, self::LOST];
    }
}
