<?php

namespace App\Service\Message;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageTypeEnum;

class MessageBuilder
{

    public function passwordRecovery(User $user): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::PWD_RECOVERY->value);
        $message->setScheduledAt(new \DateTimeImmutable('now'));
        $message->setContent('Usa el siguiente código para acceder a tu cuenta: ' . $user->getAccessCode());

        return $message;
    }

}