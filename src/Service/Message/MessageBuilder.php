<?php

namespace App\Service\Message;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageTypeEnum;
use App\Service\Time\ClockService;

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
        $message->setScheduledAt((new \DateTimeImmutable('today'))->setTime(9, 0));
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
        $message->setScheduledAt((new \DateTimeImmutable('today'))->setTime(9, 0));
        $message->setContent(
            "¡Feliz cumpleaños {$user->getName()}! Te deseamos un día lleno de sorpresas y no olvides celebrar al máximo... e invitarnos :)",
        );

        return $message;
    }

    public function merryChristmas(User $user, \DateTimeImmutable $scheduled = null): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::CHRISTMAS_GREETING->value);
        $message->setScheduledAt($scheduled ?? (new \DateTimeImmutable('today'))->setTime(18, 0));
        $message->setContent(
            "¡Feliz Navidad {$user->getName()}! Que la magia de esta temporada llene tu hogar de alegría, amor y sobre todo baile!.",
        );

        return $message;
    }

    public function newYearsEve(mixed $user, \DateTimeImmutable $scheduled = null): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::NEW_YEAR_GREETING->value);
        $message->setScheduledAt($scheduled ?? (new \DateTimeImmutable('today'))->setTime(23, 00));
        $message->setContent(
            "¡Feliz Año Nuevo {$user->getName()}! Que este nuevo año te traiga mucho éxito y mucho baile... y fiestas épicas.",
        );

        return $message;
    }

    public function createLoanReturnNoticeMessage(User $user, string $date): Message
    {
        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::LOAN_RETURN_NOTICE->value);
        $message->setScheduledAt((new \DateTimeImmutable('now'))->setTime(10, 0));
        $message->setContent(
            "Hola {$user->getName()}, aviso devolución de vestuario el día: $date. Contamos contigo, si no, por favor gestiona con ayuda de alguien. Fecha única. Gracias",
        );

        return $message;
    }

    public function createLoanReturnReminderMessage(User $user, \DatetimeInterface $date): Message
    {
        $clock = new ClockService();

        $returnDay = match (true) {
            $date->format('Ymd') === $clock->today()->format('Ymd') => 'hoy',
            $date->format('Ymd') === $clock->tomorrow()->format('Ymd') => 'mañana',
            default => $date->format('d/m/Y')
        };

        $message = new Message();
        $message->setUser($user);
        $message->setType(MessageTypeEnum::LOAN_RETURN_REMINDER->value);
        $message->setScheduledAt((new \DateTimeImmutable('now'))->setTime(9, 0));
        $message->setContent(
            "Hola {$user->getName()}, recordatorio amistoso de que la devolución de vestuario es $returnDay! No olvides tener todo listo y gracias por tu colaboración :)",
        );

        return $message;
    }


}