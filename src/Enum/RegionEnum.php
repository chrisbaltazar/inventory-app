<?php

namespace App\Enum;

enum RegionEnum: string
{
    use WithValues;

    case JALISCO = 'JALISCO';
    case HUASTECA = 'HUASTECA';
    case HIDALGO = 'HIDALGO';
    case OAXACA = 'OAXACA';
    case QUETERARO = 'QUETERARO';
}
