<?php

namespace App\Enum;

enum MessageStatusEnum: string
{
    case SENT = 'SENT';
    case ERROR = 'ERROR';
    case CANCELED = 'CANCELED';
}
