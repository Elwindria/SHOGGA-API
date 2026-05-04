<?php

namespace App\Command;

use App\Service\Sellsy\SellsyV1Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Service\Sellsy\Tax\SellsyTaxService;
use App\Service\Sellsy\Staff\SellsyStaffService;

#[AsCommand(
    name: 'app:sellsy:get-info',
    description: 'Récupère des infos Sellsy utile pour le développement de l API (tax id, collaborator id...)'
)]
class SellsyGetInfoCommand extends Command
{

    private const TYPES_ARRAY = [
        'tax',
        'staffs',
    ];

    public function __construct(
        private SellsyV1Client $sellsyV1Client,
        private SellsyTaxService $sellsyTaxService,
        private SellsyStaffService $sellsyStaffService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = strtolower($input->getArgument('type'));

        return match ($type) {
            self::TYPES_ARRAY[0] => $this->handleTaxes($io),
            self::TYPES_ARRAY[1] => $this->handleStaffs($io),
            default => $this->handleUnknown($io, $type),
        };
    }

    private function handleTaxes(SymfonyStyle $io): int
    {
        $taxes = $this->sellsyTaxService->getTaxes();

        foreach ($taxes as $tax) {
            $io->writeln(sprintf(
                'ID: %s | Nom: %s | Taux: %s',
                $tax['id'] ?? '',
                $tax['name'] ?? '',
                $tax['rate'] ?? ''
            ));
        }

        return Command::SUCCESS;
    }

    private function handleStaffs(SymfonyStyle $io): int
    {
        $staffs = $this->sellsyStaffService->getStaffs();

        foreach ($staffs as $s) {
            $io->writeln(sprintf(
                'ID: %s | Nom: %s',
                $s['id'] ?? '',
                $s['fullname'] ?? ''
            ));
        }

        return Command::SUCCESS;
    }

    private function handleUnknown(SymfonyStyle $io, string $type): int
    {
        $io->error("Type inconnu: $type");
        $io->writeln('Types disponibles:');
        $io->listing(self::TYPES_ARRAY);

        return Command::FAILURE;
    }
}