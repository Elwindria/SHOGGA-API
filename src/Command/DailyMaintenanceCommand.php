<?php

namespace App\Command;

use App\GameContest\Service\GameContestRGPDCleanupService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:daily-maintenance',
    description: 'Execute all daily maintenance tasks',
)]
final class DailyMaintenanceCommand extends Command
{
    public function __construct(
        private readonly GameContestRGPDCleanupService $gameContestRGPDCleanupService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->logger->info('[Maintenance] Daily maintenance started');

        try {
            $deletedIndividuals = $this->gameContestRGPDCleanupService->cleanExpiredIndividualsFromGameContest();

            $deletedEmails = $this->gameContestRGPDCleanupService->cleanEmailAttempt();

            $this->logger->info('[Maintenance] Daily maintenance completed', [
                'deleted_individuals' => $deletedIndividuals,
                'deleted_emails' => $deletedEmails,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->logger->error('[Maintenance] Daily maintenance failed', [
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }
    }
}