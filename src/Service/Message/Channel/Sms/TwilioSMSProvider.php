<?php

namespace App\Service\Message\Channel\Sms;

use Twilio\Rest\Client;

class TwilioSMSProvider implements SMSProviderInterface
{

    public function __construct(
        private readonly Client $twilio,
    ) {}

    public function send(string $number, string $sender, string $message): mixed
    {
        return $this->twilio->messages->create(
            $number,
            [
                'from' => $sender,
                'body' => $message,
            ],
        );
    }

}