<?php

namespace App\Subscriber;

use App\Enum\MessageTypeEnum;
use App\Event\MessageProcessedEvent;
use App\Service\Message\Channel\SMSMessageAdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSMessageChannelSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private SMSMessageAdapterInterface $sms,
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