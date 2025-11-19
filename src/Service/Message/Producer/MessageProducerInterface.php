<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;

interface MessageProducerInterface
{

    public function existMessage(...$args): ?Message;


    public function isRelevant(?Message $message): bool;

}