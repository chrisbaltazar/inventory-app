<?php

namespace App\Service\Message;

use App\Enum\MessageTypeEnum;
use App\Event\MessageProcessedEvent;
use App\Repository\MessageRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageManagerService
{

    public function __construct(
        private MessageRepository $messageRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function processAllPending(): void
    {
        foreach ($this->messageRepository->findAllPending() as $message) {
            $messageType = MessageTypeEnum::from($message->getType());
            $this->eventDispatcher->dispatch(new MessageProcessedEvent($messageType, $message));
        }
    }

}