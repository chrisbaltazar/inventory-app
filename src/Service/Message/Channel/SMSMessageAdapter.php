<?php

namespace App\Service\Message\Channel;

use App\Entity\Message;

class SMSMessageAdapter implements MessageAdapterInterface
{
    public function handle(Message $message): void {}
}