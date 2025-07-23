<?php

namespace App\Enum;

trait WithEnumValues
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}