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

    public function adminBirthdayMessage(User $user, string $name): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF->value);
        $message->setScheduledAt(new \DateTimeImmutable('now'));
        $message->setContent(
            "Hoy es el cumpleaños de $name. No olvides enviarle tus felicitaciones... y quizá unas chelas!",
        );

        return $message;
    }

    public function userBirthdayMessage(User $user): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::USER_BIRTHDAY_GREET->value);
        $message->setScheduledAt(new \DateTimeImmutable('now'));
        $message->setContent(
            "¡Feliz cumpleaños {$user->getName()}! Te deseamos un día lleno de sorpresas y no olvides celebrar al máximo... e invitarnos :)",
        );

        return $message;
    }


}