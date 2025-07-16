<?php

namespace App\Enum;

trait WithValues
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}