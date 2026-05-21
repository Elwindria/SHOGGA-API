<?php

namespace App\Command;

use App\GameContest\GameContestSubmissionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-game-contest',
    description: 'Test la création d’un prospect Sellsy depuis le flow GameContest.',
)]

final class GameContestCommand extends Command
{
    public function __construct(
        private readonly GameContestSubmissionService $submissionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $payload = [
            'email' => 'p.lopez2000@laposte.net',
            'hasWon' => true,
            'rewardType' => '-20%',
            'newsletter' => false,
            'rgpd' => true,
        ];

        try {
            // $this->submissionService->handle($payload);
            $this->submissionService->handleHasWon($payload);

            $output->writeln('<info>Prospect créé avec succès + envoit du mail</info>');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));

            return Command::FAILURE;
        }
    }
}