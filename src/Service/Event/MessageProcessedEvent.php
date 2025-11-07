<?php

namespace App\Service\Event;

use App\Entity\Message;
use App\Service\Message\MessageTypeEnum;


final class MessageProcessedEvent
{
    public function __construct(
        public readonly MessageTypeEnum $messageType,
        public readonly Message $message,
    ) {}
}