<?php

namespace App\Enum;

enum SizeEnum: string
{
    use WithEnumValues;

    case UNIQUE = 'ÚNICA';
    case SMALL = 'SM';
    case MEDIUM = 'MD';
    case LARGE = 'LG';
    case XLARGE = 'XL';
}
