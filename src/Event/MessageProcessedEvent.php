<?php

namespace App\Event;

use App\Entity\Message;
use App\Enum\MessageTypeEnum;


final class MessageProcessedEvent
{
    public function __construct(
        public readonly MessageTypeEnum $messageType,
        public readonly Message $message,
    ) {}
}