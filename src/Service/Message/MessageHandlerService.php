<?php

namespace App\Service\Message;

use App\Repository\MessageRepository;
use App\Service\Event\MessageProcessedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageHandlerService
{

    public function __construct(
        private MessageRepository $messageRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function processAllPending(): void
    {
        foreach ($this->messageRepository->findAllPending() as $message) {
            $this->eventDispatcher->dispatch(new MessageProcessedEvent($message));
        }
    }

}