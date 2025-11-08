<?php

namespace App\Enum;

enum MessageTypeEnum: string
{
    case PWD_RECOVERY = 'PWD_RECOVERY';

    public function isPwdRecovery(): bool
    {
        return $this === self::PWD_RECOVERY;
    }
}
