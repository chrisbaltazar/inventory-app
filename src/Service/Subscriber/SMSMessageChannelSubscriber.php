<?php

namespace App\Service\Subscriber;

use App\Service\Event\MessageProcessedEvent;
use App\Service\Message\Channel\SMSMessageAdapter;
use App\Service\Message\MessageTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSMessageChannelSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private SMSMessageAdapter $sms,
        private EntityManagerInterface $entityManager,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [MessageProcessedEvent::class => 'onMessageProcessed'];
    }

    public function onMessageProcessed(MessageProcessedEvent $event): void
    {
        if (!$this->isMessageSMS($event->messageType)) {
            return;
        }

        $this->sms->handle($event->message);
    }

    private function isMessageSMS(MessageTypeEnum $messageType): bool
    {
        return match ($messageType) {
            MessageTypeEnum::PWD_RECOVERY => true,
            default => false,
        };
    }

}