<?php

namespace App\Service\Message\Channel\Sms;

interface SMSProviderInterface
{

    public function send(string $number, string $sender, string $message): mixed;

}