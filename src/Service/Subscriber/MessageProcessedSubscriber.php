<?php

namespace App\Service\Subscriber;

use App\Service\Event\MessageProcessedEvent;
use App\Service\Message\Channel\SMSMessageAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageProcessedSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private SMSMessageAdapter $sms,
    ) {}

    public static function getSubscribedEvents()
    {
        return [MessageProcessedEvent::class => 'onMessageProcessed'];
    }

    public function onMessageProcessed(MessageProcessedEvent $event)
    {
        $this->sms->handle($event->getMessage());
    }
}