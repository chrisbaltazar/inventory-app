<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Enum\MessageTypeEnum;

interface MessageProducerInterface
{

    /**
     * Creates new messages based on business logic
     */
    public function produce(): void;

    /**
     * Determines if a previous message with the same arguments already exists
     */
    public function existMessage(...$args): ?Message;

    /**
     * Determines if the given message type should be handled by the current producer
     */
    public function isRegistered(MessageTypeEnum $messageType): bool;

    /**
     * Determines whether the given message is still relevant for processing to prevent duplicates.
     */
    public function isRelevant(Message $message): bool;

    /**
     * Determines whether the given message is expired and should not be processed anymore
     */
    public function isExpired(Message $message): bool;
}