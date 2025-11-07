<?php

namespace App\Service\Event;

use App\Entity\Message;

final class MessageProcessed
{

    public function __construct(
        private readonly Message $message,
    ) {}

    public function getMessage(): Message
    {
        return $this->message;
    }
}