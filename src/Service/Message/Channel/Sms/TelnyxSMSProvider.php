<?php

namespace App\Service\Message\Channel\Sms;

use Symfony\Component\HttpClient\RetryableHttpClient;

class TelnyxSMSProvider implements SmsProviderInterface
{

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $messagingProfileId,
        private readonly RetryableHttpClient $retryClient,
    ) {}

    public function send(string $number, string $sender, string $message): mixed
    {
        $uri = sprintf('%s/%s', rtrim($this->baseUrl, '/'), 'messages');

        return $this->retryClient->request(
            method: 'POST',
            url: $uri,
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
            ],
        );
    }
}