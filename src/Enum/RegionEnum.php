<?php

namespace App\Enum;

enum RegionEnum: string
{
    use WithEnumValues;

    case JALISCO = 'JALISCO';
    case HIDALGO = 'HIDALGO';
    case OAXACA = 'OAXACA';
    case QUERETARO = 'QUERÉTARO';
    case VERACRUZ = 'VERACRÚZ';
}
