<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Sellsy\Individual\SellsyIndividualService;

#[AsCommand(
    name: 'app:shogga:game-contest:delete-rgpd-expired-individuals',
    description: 'Delete expired individuals from GameContest (3 year) cause of RGPD',
)]
class ShoggaGameContestDeleteRgpdExpiredContactsCommand extends Command
{
    public function __construct(
        private readonly SellsyIndividualService $sellsyIndividualService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $expiredIndividuals = $this->sellsyIndividualService->findExpiredIndividualsFromGameContest();

        foreach ($expiredIndividuals['data'] as $individual) {
            $this->sellsyIndividualService->deleteIndividual($individual['id']);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
