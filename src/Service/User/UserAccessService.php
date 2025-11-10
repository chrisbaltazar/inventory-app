<?php

namespace App\Service\User;

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

    public function getAccessToken(User $user): string
    {
        if ($user->getAccessCode() && $user->getCodeExpiration() && $user->getCodeExpiration() > new \DateTime('now')) {
            return $this->createToken($user);
        }

        if (!$user->getPhone()) {
            throw new \RuntimeException('User phone number is required');
        }

        $newAccessCode = $this->getCode();
        $expirationTime = new \DateTime(self::DEFAULT_EXPIRATION_TIME);
        $user->setAccessCode($newAccessCode);
        $user->setCodeExpiration($expirationTime);
        $this->entityManager->flush();

        return $this->createToken($user);
    }

    private function getCode(): int
    {
        $min = str_pad(1, self::DEFAULT_CODE_LENGTH - 1, '0', STR_PAD_RIGHT);
        $max = str_pad(9, self::DEFAULT_CODE_LENGTH - 1, '9', STR_PAD_RIGHT);

        return random_int((int) $min, (int) $max);
    }

    public function getAccessData(string $token): UserAccessData
    {
        $decoded = base64_decode($token);
        if (!$decoded) {
            throw new \RuntimeException('Invalid token');
        }

        $parts = explode('|', $decoded);

        return new UserAccessData(
            new \DateTime($parts[0]),
            $parts[1],
        );
    }

    private function createToken(User $user): string
    {
        return base64_encode(
            sprintf(
                '%s|%s',
                $user->getCodeExpiration()->format('Y-m-d H:i:s'),
                substr($user->getPhone(), -4),
            ),
        );
    }

}