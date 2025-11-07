<?php

namespace App\Service\Message;

use App\Repository\MessageRepository;
use App\Service\Event\MessageProcessed;
use Symfony\Component\EventDispatcher\EventDispatcher;

class MessageHandlerService
{

    public function __construct(
        private MessageRepository $messageRepository,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function sendAllPending(): void
    {
        foreach ($this->messageRepository->findAllPending() as $message) {
            $this->eventDispatcher->dispatch(new MessageProcessed($message));
        }
    }

}