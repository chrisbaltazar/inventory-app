<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserAccessService
{

    const DEFAULT_EXPIRATION_TIME = '+5 min';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}


    public function __invoke(array $search, int $codeLength): User
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

        $newAccessCode = $this->getCode($codeLength);
        $expirationTime = new \DateTime(self::DEFAULT_EXPIRATION_TIME);
        $user->setAccessCode($newAccessCode);
        $user->setCodeExpiration($expirationTime);
        $this->entityManager->flush();

        return $user;
    }

    private function getCode(int $size): int
    {
        $min = str_pad(1, $size, '0', STR_PAD_RIGHT);
        $max = str_pad(9, $size, '9', STR_PAD_RIGHT);

        return random_int((int) $min, (int) $max);
    }
}