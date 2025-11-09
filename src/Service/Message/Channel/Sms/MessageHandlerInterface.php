<?php

namespace App\Service\Message\Channel\Sms;

use App\Entity\Message;

interface MessageHandlerInterface {

    public function handle(Message $message): void;
}