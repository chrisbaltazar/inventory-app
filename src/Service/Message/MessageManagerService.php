<?php

namespace App\Service\Message;

use App\Entity\Message;
use App\Enum\MessageTypeEnum;
use App\Event\MessageProcessedEvent;
use App\Repository\MessageRepository;
use App\Service\Message\Producer\MessageProducerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageManagerService
{

    /**
     * @param MessageProducerInterface[] $messageProducers
     */
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly iterable $messageProducers,
    ) {}

    public function produceNew(): void
    {
        foreach ($this->messageProducers as $messageProducer) {
            $messageProducer->produce();
        }
    }

    public function processAllPending(): void
    {
        foreach ($this->messageRepository->findAllPending() as $message) {
            foreach ($this->messageProducers as $messageProducer) {
                if ($messageProducer->canBeSent($message)) {
                    $this->dispatch($message);
                    break;
                }
            }
        }
    }

    public function dispatch(Message $message): void
    {
        $messageType = MessageTypeEnum::from($message->getType());
        $this->eventDispatcher->dispatch(new MessageProcessedEvent($messageType, $message));
    }

}