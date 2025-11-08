<?php

namespace App\Service\Message\Channel;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

class TwilioSMSAdapter implements SMSMessageAdapterInterface
{
    const MAX_MESSAGE_LENGTH = 160;

    public function __construct(
        private readonly Client $client,
        private readonly string $senderNumber,
        private readonly string $appName,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function handle(Message $message): void
    {
        try {
            $recipient = $this->getMessageRecipient($message);
            $messageBody = $this->getMessageBody($message);
            $this->sendMessage($recipient, $messageBody);
            $this->markAsSent($message);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }


    private function getMessageBody(Message $message): string
    {
        $sender = $message->getSender() ?? $this->appName;

        return substr(sprintf('%s: %s', $sender, $message->getContent()), 0, self::MAX_MESSAGE_LENGTH);
    }


    private function getMessageRecipient(Message $message): string
    {
        return $message->getRecipient() ?? $message->getUser()?->getPhone() ?? throw new \UnexpectedValueException(
            'No recipient or user phone to send SMS message: ' . $message->getId(),
        );
    }

    private function sendMessage(string $recipient, string $body): void
    {
        $this->client->messages->create(
            $recipient,
            [
                'from' => $this->senderNumber,
                'body' => $body,
            ],
        );
    }

    private function markAsSent(Message $message): void
    {
        $message->setProcessedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

}