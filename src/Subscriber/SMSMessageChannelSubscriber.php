<?php

namespace App\Subscriber;

use App\Enum\MessageTypeEnum;
use App\Event\MessageProcessedEvent;
use App\Service\Message\Channel\Sms\SMSMessageHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSMessageChannelSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private SMSMessageHandler $sms,
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
            MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF => true,
            MessageTypeEnum::USER_BIRTHDAY_GREET => true,
            MessageTypeEnum::CHRISTMAS_GREETING => true,
            default => false,
        };
    }

}