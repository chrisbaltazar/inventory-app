<?php

namespace App\Service\Message\Channel\Sms;

use Telnyx\Client;

class TelnyxSMSProvider implements SmsProviderInterface
{

    public function __construct(
        private readonly Client $client,
        private readonly string $messagingProfileId,
    ) {}

    public function send(string $number, string $sender, string $message): mixed
    {
        return $this->client->messages->send(
            to: $number,
            messagingProfileID: $this->messagingProfileId,
            text: $message,
        );
    }
}