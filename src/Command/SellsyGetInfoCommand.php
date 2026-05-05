<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Service\Sellsy\Tax\SellsyTaxService;
use App\Service\Sellsy\Staff\SellsyStaffService;
use App\Service\Sellsy\Catalogue\SellsyCatalogueService;

#[AsCommand(
    name: 'app:sellsy:get-info',
    description: 'Récupère des infos Sellsy utile pour le développement de l API (tax id, collaborator id...)'
)]
class SellsyGetInfoCommand extends Command
{

    private const TYPES_ARRAY = [
        'taxes',
        'staffs',
        'catalogue',
    ];

    public function __construct(
        private SellsyTaxService $sellsyTaxService,
        private SellsyStaffService $sellsyStaffService,
        private SellsyCatalogueService $sellsyCatalogueService
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
            self::TYPES_ARRAY[2] => $this->handleCatalogue($io),
            default => $this->handleUnknown($io, $type),
        };
    }

    private function handleTaxes(SymfonyStyle $io): int
    {
        $taxes = $this->sellsyTaxService->getTaxes();

        foreach ($taxes as $tax) {
            $io->writeln(sprintf(
                'ID: %s | Taux: %s',
                $tax['id'] ?? '',
                $tax['formatted_value'] ?? '',
            ));
        }

        return Command::SUCCESS;
    }

    private function handleStaffs(SymfonyStyle $io): int
    {
        $staffs = $this->sellsyStaffService->getStaffs();

        foreach ($staffs['result'] ?? [] as $staff) {
            $io->writeln(sprintf(
                'ID: %s | fullName: %s',
                $staff['linkedid'] ?? '',
                $staff['fullName'] ?? ''
            ));
        }

        return Command::SUCCESS;
    }

    private function handleCatalogue(SymfonyStyle $io): int
    {
        $catalogue = $this->sellsyCatalogueService->getCatalogue();

        foreach ($catalogue['result'] ?? [] as $c) {
            $io->writeln(sprintf(
                'ID: %s | Nom du produit : %s',
                $c['id'] ?? '',
                $c['tradename'] ?? ''
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