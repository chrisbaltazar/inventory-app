<?php

namespace App\Enum;

enum GenderEnum: string
{
    use WithEnumValues;

    case W = 'Mujer';
    case M = 'Hombre';
    case U = 'Unisex';
}
