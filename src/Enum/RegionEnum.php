<?php

namespace App\Enum;

enum RegionEnum: string
{
    use WithEnumValues;

    case ACCESORIOS = 'ACCESORIOS';
    case ENSAYO = 'ENSAYO';
    case JALISCO = 'JALISCO';
    case HIDALGO = 'HIDALGO';
    case OAXACA = 'OAXACA';
    case VERACRUZ = 'VERACRÚZ';
    case NAYARIT = 'NAYARIT';
    case YUCATAN = 'YUCATÁN';
    case CHIAPAS = 'CHIAPAS';
    case NORTE = 'NORTE';
    case MICHOACAN = 'MICHOACÁN';
    case REVOLUCION = 'REVOLUCIÓN';
    case GUERRERO = 'GUERRERO';
    case PREHISPANICO = 'PREHISPÁNICO';
    case BAJA_CALIFORNIA = 'BAJA CALIFORNIA';
}
