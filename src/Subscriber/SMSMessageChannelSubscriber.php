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
            MessageTypeEnum::PWD_RECOVERY,
            MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF,
            MessageTypeEnum::USER_BIRTHDAY_GREET,
            MessageTypeEnum::CHRISTMAS_GREETING,
            MessageTypeEnum::NEW_YEAR_GREETING,
            MessageTypeEnum::LOAN_RETURN_NOTICE,
            MessageTypeEnum::LOAN_RETURN_REMINDER,
            MessageTypeEnum::LOAN_DELAYED_REMINDER => true,
            default => false,
        };
    }

}