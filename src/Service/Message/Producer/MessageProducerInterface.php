<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;

interface MessageProducerInterface
{

    public function produce(): void;

    public function existMessage(...$args): ?Message;

    public function canBeCreated(Message $message): bool;

    public function canBeSent(Message $message): bool;

}