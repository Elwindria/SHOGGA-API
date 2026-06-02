<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Sellsy\Individual\SellsyIndividualService;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'app:shogga:game-contest:delete-rgpd-expired-individuals',
    description: 'Delete expired SHOGGA Game Contest individuals after 3 years for GDPR compliance',
)]
class ShoggaGameContestDeleteRgpdExpiredIndividualsCommand extends Command
{
    public function __construct(
        private readonly SellsyIndividualService $sellsyIndividualService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;

        $this->logger->info(
            '[Sellsy][GameContest] Starting SHOGGA Game Contest RGPD cleanup'
        );

        try {
            $expiredIndividuals = $this->sellsyIndividualService->findExpiredIndividualsFromGameContest();

            foreach ($expiredIndividuals['data'] ?? [] as $individual) {
                $this->sellsyIndividualService->deleteIndividual($individual['id']);
                $count++;

                $this->logger->info(
                    '[Sellsy][GameContest] Deleted expired SHOGGA individual',
                    [
                        'individual_id' => $individual['id'],
                        'email' => $individual['email'] ?? null,
                    ]
                );
            }

            if ($count === 0) {
                $io->success('Aucun particulier expiré du jeu concours SHOGGA trouvé.');
            } else {
                $io->success(sprintf(
                    '%d contacts du jeu concours SHOGGA ont été supprimés (durée de conservation dépassée).',
                    $count,
                ));
            }

            $this->logger->info(
                '[Sellsy][GameContest] SHOGGA Game Contest RGPD cleanup completed',
                [
                    'deleted_count' => $count,
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->logger->error(
                '[Sellsy][GameContest] SHOGGA Game Contest RGPD cleanup failed',
                [
                    'exception' => $e,
                ]
            );

            return Command::FAILURE;
        }
    }
}