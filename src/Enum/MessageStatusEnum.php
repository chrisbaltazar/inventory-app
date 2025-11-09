<?php

namespace App\Enum;

enum MessageStatusEnum: string
{
    use WithEnumValues;

    case SENT = 'SENT';
    case ERROR = 'ERROR';
    case CANCELED = 'CANCELED';
}
