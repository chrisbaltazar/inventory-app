<?php

namespace App\Service\Message\Channel;

use App\Entity\Message;
use Twilio\Rest\Client;

class TwilioSMSAdapter implements SMSMessageAdapterInterface
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function handle(Message $message): void {}
}