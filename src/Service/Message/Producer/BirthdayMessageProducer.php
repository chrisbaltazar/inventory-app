<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;

class BirthdayMessageProducer implements MessageProducerInterface
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageRepository $messageRepository,
        private readonly MessageBuilder $messageBuilder,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $birthdayUsers = $this->userRepository->findUsersWithBirthday(date('m'), date('d'));
        if (empty($birthdayUsers)) {
            return;
        }

        foreach ($birthdayUsers as $user) {
            $existingMessage = $this->existMessage(MessageTypeEnum::USER_BIRTHDAY_GREET, $user, $user->getName());
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->userBirthdayMessage($user);
            $this->entityManager->persist($message);
            $this->createAdminMessages($user);
        }

        $this->entityManager->flush();
    }

    private function createAdminMessages(User $user): void
    {
        $admins = $this->userRepository->findAllAdmin();
        foreach ($admins as $admin) {
            if ($admin->getId() === $user->getId()) {
                continue;
            }

            $existingMessage = $this->existMessage(MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF, $admin, $user->getName());
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->adminBirthdayMessage($admin, $user->getName());
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();
    }

    public function existMessage(...$args): ?Message
    {
        /** @var MessageTypeEnum $type */
        /** @var User $user */
        [$type, $user, $name] = $args;

        return $this->messageRepository->findOneWith(
            type: $type,
            user: $user,
            scheduled: new \DateTime('now'),
            content: $name,
        );
    }

    public function isRelevant(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return ($type->isAdminBirthdayNotif() || $type->isUserBirthdayGreet())
            && $message->getScheduledAt()?->format('Y-m-d') === (new \DateTime('now'))->format('Y-m-d')
            && (!$message->getStatus() || $message->getStatus() !== MessageStatusEnum::ERROR->value);
    }

    public function isWaiting(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return ($type->isAdminBirthdayNotif() || $type->isUserBirthdayGreet())
            && $message->getScheduledAt()?->format('Y-m-d') === (new \DateTime('now'))->format('Y-m-d')
            && !$message->getStatus();
    }
}