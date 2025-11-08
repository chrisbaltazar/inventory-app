<?php

namespace App\Service\Message\Channel;

use App\Entity\Message;

interface SMSMessageAdapterInterface {

    public function handle(Message $message): void;
}