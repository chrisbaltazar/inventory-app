<?php

namespace App\Enum;

enum MessageTypeEnum: string
{
    use WithEnumValues;

    case PWD_RECOVERY = 'PWD_RECOVERY';
    case ADMIN_BIRTHDAY_NOTIF = 'ADMIN_BIRTHDAY_NOTIF';

    public function isPwdRecovery(): bool
    {
        return $this === self::PWD_RECOVERY;
    }

    public function isAdminBirthdayNotif(): bool
    {
        return $this === self::ADMIN_BIRTHDAY_NOTIF;
    }
}
