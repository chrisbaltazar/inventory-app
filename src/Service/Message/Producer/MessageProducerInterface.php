<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;

interface MessageProducerInterface
{

    public function produce(): void;

    public function existMessage(...$args): ?Message;

    public function isRelevant(Message $message): bool;

}