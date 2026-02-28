<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;

interface MessageProducerInterface
{

    public function produce(): void;

    public function existMessage(...$args): ?Message;

    /**
     * Determines whether the given message is still relevant for processing to prevent duplicates.
     */
    public function isRelevant(Message $message): bool;

}