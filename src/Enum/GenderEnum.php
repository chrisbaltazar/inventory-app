<?php

namespace App\Enum;

enum GenderEnum: string
{
    use WithEnumValues;

    case W = 'Mujer';
    case M = 'Hombre';
    case U = 'Unisex';

    public function isMale(): bool
    {
        return $this === self::M;
    }

    public function isFemale(): bool
    {
        return $this === self::W;
    }
}
