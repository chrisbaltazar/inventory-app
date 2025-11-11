<?php

namespace App\Service\User;

use App\Dto\User\UserAccessData;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserAccessService
{

    const DEFAULT_EXPIRATION_TIME = '+5 min';
    const DEFAULT_CODE_LENGTH = 6;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}


    public function make(array $search): User
    {
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

        $newAccessCode = $this->getCode();
        $expirationTime = new \DateTime(self::DEFAULT_EXPIRATION_TIME);
        $user->setAccessCode($newAccessCode);
        $user->setCodeExpiration($expirationTime);
        $this->entityManager->flush();

        return $user;
    }

    private function getCode(): int
    {
        $min = str_pad(1, self::DEFAULT_CODE_LENGTH - 1, '0', STR_PAD_RIGHT);
        $max = str_pad(9, self::DEFAULT_CODE_LENGTH - 1, '9', STR_PAD_RIGHT);

        return random_int((int) $min, (int) $max);
    }
}