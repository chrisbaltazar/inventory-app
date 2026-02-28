<?php

namespace App\Service\Message\Channel\Sms;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Telnyx\Client;

class TelnyxSMSProvider implements SmsProviderInterface
{

    public function __construct(
        private readonly string $apiKey,
        private readonly string $messagingProfileId,
        private readonly RetryableHttpClient $retryClient,
    ) {}

    public function send(string $number, string $sender, string $message): mixed
    {
            return $this->retryClient->request(
                method: 'POST',
                url: 'https://api.telnyx.com/v2/messages',
                options: [
                    'json' => [
                        'to' => $number,
                        'text' => $message,
                        'messaging_profile_id' => $this->messagingProfileId,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

//        return $this->client->messages->send(
//            to: $number,
//            messagingProfileID: $this->messagingProfileId,
//            text: $message,
//        );
    }
}