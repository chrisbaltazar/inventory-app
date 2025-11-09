<?php

namespace App\Service\Message;

use App\Entity\Message;

interface MessageHandlerInterface {

    public function handle(Message $message): void;
}