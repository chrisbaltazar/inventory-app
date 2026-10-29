<?php

namespace App\Command;

use App\Service\Message\MessageManagerService;
use DateTimeInterface;
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
        private readonly MessageManagerService $messageManager,
    ) {
        parent::__construct();
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
        $io->info($this->getLogMessage('Starting message processing...'));

        try {
            $this->messageManager->produceNew();
            $this->messageManager->processAllPending();
            $io->success($this->getLogMessage('All pending messages have been processed.'));

            return Command::SUCCESS;
        } catch (\Throwable $t) {
            $error = sprintf('%s: %s', $t->getMessage(), $t->getTraceAsString());
            $io->error($this->getLogMessage($error));

            return Command::FAILURE;
        }
    }

    private function getLogMessage(string $message): string
    {
        $date = new \DateTimeImmutable()->format(DateTimeInterface::ATOM);

        return "[$date] $message";
    }
}
