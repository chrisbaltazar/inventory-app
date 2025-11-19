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

    public function adminBirthdayMessage(User $user): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF->value);
        $message->setScheduledAt(new \DateTimeImmutable('now'));
        $message->setContent(
            "Hoy es el cumpleaños de {$user->getName()}. No olvides enviarle tus felicitaciones... y quizá unas chelas!",
        );

        return $message;
    }

}