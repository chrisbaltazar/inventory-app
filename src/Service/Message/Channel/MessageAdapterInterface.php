<?php

namespace App\Service\Message\Channel;

use App\Entity\Message;

interface MessageAdapterInterface {

    public function handle(Message $message): void;
}