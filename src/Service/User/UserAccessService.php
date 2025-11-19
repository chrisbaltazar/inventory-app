<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Message\MessageBuilder;
use App\Service\Message\MessageManagerService;
use Doctrine\ORM\EntityManagerInterface;

class UserAccessService
{

    const DEFAULT_EXPIRATION_TIME = '+5 min';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBuilder $messageBuilder,
        private readonly MessageManagerService $messageManager,
    ) {}


    public function make(array $search, int $codeLength): User
    {
        if ($codeLength <= 0) {
            throw new \InvalidArgumentException('Code length must be greater than 0');
        }

        $user = $this->userRepository->findOneBy($search);

        if (!$user) {
            throw new \UnexpectedValueException('User email not found');
        }

        if (!$user->getPhone()) {
            throw new \RuntimeException('User phone number is required');
        }

        if ($user->getAccessCode() && $user->getCodeExpiration() && $user->getCodeExpiration() > new \DateTime('now')) {
            return $user;
        }

        $this->setUserData($user, $codeLength);
        $this->createMessage($user);
        // Persist changes after message processed
        $this->entityManager->flush();

        return $user;
    }

    private function getCode(int $size): int
    {
        $min = str_pad(1, $size, '0', STR_PAD_RIGHT);
        $max = str_pad(9, $size, '9', STR_PAD_RIGHT);

        return random_int((int) $min, (int) $max);
    }

    public function validate(User $user, string $code): bool
    {
        if (!$code) {
            throw new \RuntimeException('Access code required');
        }

        $expiration = $user->getCodeExpiration();
        if (!$expiration || $expiration < new \DateTime('now')) {
            throw new \LogicException('Access code expired');
        }

        return $code === (string) $user->getAccessCode();
    }

    private function setUserData(User $user, int $codeLength): void
    {
        $newAccessCode = $this->getCode($codeLength);
        $expirationTime = new \DateTime(self::DEFAULT_EXPIRATION_TIME);
        $user->setAccessCode($newAccessCode);
        $user->setCodeExpiration($expirationTime);
    }

    private function createMessage(User $user): void
    {
        $message = $this->messageBuilder->passwordRecovery($user);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->messageManager->dispatch($message);
    }

}