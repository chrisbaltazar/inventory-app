<?php

namespace App\Service\Event;

use App\Entity\Message;


final class MessageProcessedEvent
{

    public function __construct(
        private readonly Message $message,
    ) {}

    public function getMessage(): Message
    {
        return $this->message;
    }
}