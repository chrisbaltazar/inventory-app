<?php

namespace App\Service\Message\Channel\Sms;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Service\Message\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SMSMessageHandler implements MessageHandlerInterface
{
    const MAX_MESSAGE_LENGTH = 160;

    public function __construct(
        private readonly string $appName,
        private readonly SMSProviderInterface $provider,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function handle(Message $message): void
    {
        try {
            $recipient = $this->getMessageRecipient($message);
            $messageBody = $this->getMessageBody($message);
            $sender = $message->getSender() ?? $this->appName;
            $this->provider->send($recipient, $sender, $messageBody);
            $this->markMessageAs(MessageStatusEnum::SENT, $message);
        } catch (\Exception $e) {
            $this->logger->error('Error sending SMS: ' . $e->getMessage());
            $this->markMessageAs(MessageStatusEnum::ERROR, $message, $e->getMessage());
        }
    }


    private function getMessageBody(Message $message): string
    {
        return substr($message->getContent(), 0, self::MAX_MESSAGE_LENGTH);
    }


    private function getMessageRecipient(Message $message): string
    {
        $recipientNumber = $message->getRecipient() ?? $message->getUser()?->getPhone(
        ) ?? throw new \UnexpectedValueException(
            'No recipient or user phone to send SMS message: ' . $message->getId(),
        );

        if (!preg_match('/^\+\d{11,12}$/', $recipientNumber)) {
            throw new \UnexpectedValueException('Invalid recipient phone number for SMS: ' . $recipientNumber);
        }

        if (str_starts_with($recipientNumber, '+1')) {
            // Special case due to costs of sending SMS to US numbers
            throw new \UnexpectedValueException('Invalid phone number country for SMS: ' . $recipientNumber);
        }

        return $recipientNumber;
    }

    private function markMessageAs(MessageStatusEnum $status, Message $message, ?string $error = null): void
    {
        $message->setStatus($status->value);
        $message->setProcessedAt(new \DateTimeImmutable());
        $message->setReason($error);
        $this->entityManager->flush();
    }

}