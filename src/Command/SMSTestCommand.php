<?php

namespace App\Command;

use App\Service\Message\Channel\Sms\SMSProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sms:test',
    description: 'SMS test command',
)]
class SMSTestCommand extends Command
{
    public function __construct(
        private readonly SmsProviderInterface $sms,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient number')
            ->addArgument('text', InputArgument::OPTIONAL, 'Message content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $to = $input->getArgument('to');
        $text = $input->getArgument('text') ?? 'Hello! the time is ' . (new \DateTime())->format('H:i:s');

        try {
            $this->sms->send($to, 'Sandbox', $text);
            $io->success('Message sent...');

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $io->error('Error sending message: ' . $exception->getMessage());

            return Command::FAILURE;
        }
    }
}
