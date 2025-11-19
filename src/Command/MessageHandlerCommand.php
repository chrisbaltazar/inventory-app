<?php

namespace App\Command;

use App\Service\Message\MessageManagerService;
use App\Service\Message\Producer\BirthdayMessageProducer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:message:process',
    description: 'Manual message dispatching',
)]
class MessageHandlerCommand extends Command
{
    public function __construct(
        private readonly MessageManagerService $messageHandler,
        private readonly BirthdayMessageProducer $birthdayMessageProducer,
    ) {
        parent::__construct();
    }

    private function consume(): void
    {
        $this->messageHandler->processAllPending();
    }


    private function produce(): void
    {
        $this->birthdayMessageProducer->createAdminMessages();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->produce();
            $this->consume();
            $io->success('All pending messages have been processed.');

            return Command::SUCCESS;
        } catch (\Throwable $t) {
            $error = sprintf('%s: %s', $t->getMessage(), $t->getTraceAsString());
            $io->error($error);

            return Command::FAILURE;
        }
    }
}
