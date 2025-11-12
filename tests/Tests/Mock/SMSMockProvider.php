<?php

namespace Tests\Mock;

use App\Service\Message\Channel\Sms\SMSProviderInterface;

class SMSMockProvider implements SMSProviderInterface {

    public function send(string $number, string $sender, string $message): mixed
    {
        // TODO: Implement send() method.
    }

}