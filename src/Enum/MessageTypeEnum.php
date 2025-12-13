<?php

namespace App\Enum;

enum MessageTypeEnum: string
{
    use WithEnumValues;

    case PWD_RECOVERY = 'PWD_RECOVERY';
    case ADMIN_BIRTHDAY_NOTIF = 'ADMIN_BIRTHDAY_NOTIF';
    case USER_BIRTHDAY_GREET = 'USER_BIRTHDAY_GREET';
    case CHRISTMAS_GREETING = 'CHRISTMAS_GREETING';
    case NEW_YEAR_GREETING = 'NEW_YEAR_GREETING';
    case LOAN_RETURN_NOTICE = 'LOAN_RETURN_NOTICE';

    public function isPwdRecovery(): bool
    {
        return $this === self::PWD_RECOVERY;
    }

    public function isAdminBirthdayNotif(): bool
    {
        return $this === self::ADMIN_BIRTHDAY_NOTIF;
    }

    public function isUserBirthdayGreet(): bool
    {
        return $this === self::USER_BIRTHDAY_GREET;
    }

    public function isChristmasGreeting(): bool
    {
        return $this === self::CHRISTMAS_GREETING;
    }

    public function isNewYearGreeting(): bool
    {
        return $this === self::NEW_YEAR_GREETING;
    }

    public function isLoanReturnNotice(): bool
    {
        return $this === self::LOAN_RETURN_NOTICE;
    }
}
