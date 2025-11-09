<?php

namespace App\DataFixtures\Factory;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use DateTimeInterface;

class MessageFactory extends AbstractFactory
{

    public static function create(
        MessageTypeEnum $type = null,
        User $user = null,
        string $recipient = null,
        string $subject = null,
        string $content = null,
        MessageStatusEnum $status = null,
        DatetimeInterface $createdAt = null,
        DatetimeInterface $scheduledAt = null,
        DatetimeInterface $processedAt = null,
    ): Message {
        $message = new Message();
        $message->setType($type?->value ?? self::faker()->randomElement(MessageTypeEnum::values()));
        $message->setUser($user ?? UserFactory::create());
        $message->setRecipient($recipient ?? self::faker()->phoneNumber());
        $message->setSubject($subject ?? self::faker()->sentence);
        $message->setContent($content ?? self::faker()->paragraph);
        $message->setStatus($status?->value ?? self::faker()->randomElement(MessageStatusEnum::values()));
        $message->setCreatedAt(
            $createdAt ?? new \DateTimeImmutable(self::faker()->dateTimeBetween('-3 days', 'now')->format('Ymd H:i:s')),
        );
        $message->setScheduledAt(
            $scheduledAt ?? new \DateTimeImmutable(
            self::faker()->dateTimeBetween('now', '+3 days')->format('Ymd H:i:s'),
        ));
        $message->setProcessedAt(
            $processedAt ?? new \DateTimeImmutable(
            self::faker()->dateTimeBetween('+3 days', '+5 days')->format('Ymd H:i:s'),
        ));

        return $message;
    }

}