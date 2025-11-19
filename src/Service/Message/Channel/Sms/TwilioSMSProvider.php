<?php

namespace App\Service\Message\Channel\Sms;

use Twilio\Rest\Client;

class TwilioSMSProvider implements SMSProviderInterface
{

    public function __construct(
        private readonly Client $twilio,
        private readonly string $fromNumber,
    ) {}

    public function send(string $number, string $sender, string $message): mixed
    {
        return $this->twilio->messages->create(
            $number,
            [
                'from' => $this->fromNumber,
                'body' => sprintf('%s: %s', $sender, $message),
            ],
        );
    }

}