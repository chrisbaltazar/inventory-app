<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageTypeEnum;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageComposer;
use App\Service\Time\TimeDiff;
use Doctrine\ORM\EntityManagerInterface;

class BirthdayMessageProducer implements MessageProducerInterface
{
    use TimeDiff;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageRepository $messageRepository,
        private readonly MessageComposer $messageComposer,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $birthdayUsers = $this->userRepository->findUsersWithBirthday(date('m'), date('d'));
        if (empty($birthdayUsers)) {
            return;
        }

        foreach ($birthdayUsers as $user) {
            $existingMessage = $this->existMessage([
                'type' => MessageTypeEnum::USER_BIRTHDAY_GREET,
                'user' => $user,
                'scheduled' => new \DateTime('now'),
            ]);
            if ($existingMessage) {
                continue;
            }

            $message = $this->messageComposer->userBirthdayMessage($user);
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

            $existingMessage = $this->existMessage([
                'type' => MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF,
                'user' => $user,
                'scheduled' => new \DateTime('now'),
                'keyword' => $user->getEmail(),
            ]);
            if ($existingMessage) {
                continue;
            }

            $message = $this->messageComposer->adminBirthdayMessage($admin, $user->getName());
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();
    }

    public function existMessage(array $data): ?Message
    {
        return $this->messageRepository->findOneWith(... $data);
    }

    public function isRegistered(MessageTypeEnum $messageType): bool
    {
        return $messageType->isUserBirthdayGreet() || $messageType->isAdminBirthdayNotif();
    }

    public function isExpired(Message $message): bool
    {
        return $this->getDiffHours($message->getScheduledAt()) > 12;
    }
}