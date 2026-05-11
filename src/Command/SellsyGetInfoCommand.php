<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Sellsy\Tax\SellsyTaxService;
use App\Sellsy\Staff\SellsyStaffService;
use App\Sellsy\Catalogue\SellsyCatalogueService;
use App\Sellsy\Company\SellsyCompanyService;
use App\Sellsy\Supplier\SellsySupplierService;

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
        'supplier',
        'companies',
    ];

    public function __construct(
        private SellsyTaxService $sellsyTaxService,
        private SellsyStaffService $sellsyStaffService,
        private SellsyCatalogueService $sellsyCatalogueService,
        private SellsySupplierService $sellsySupplierService,
        private SellsyCompanyService $sellsyCompanyService,
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
            self::TYPES_ARRAY[3] => $this->handleSupplier($io),
            self::TYPES_ARRAY[4] => $this->handleCompanies($io),
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

    private function handleSupplier(SymfonyStyle $io): int
    {
        $supplier = $this->sellsySupplierService->getSupplier();

        foreach ($supplier as $s) {
            $io->writeln(sprintf(
                'ID: %s | Nom : %s',
                $s['id'] ?? '',
                $s['name'] ?? ''
            ));
        }

        return Command::SUCCESS;
    }

    private function handleCompanies(SymfonyStyle $io): int
    {
        $companies = $this->sellsyCompanyService->getCompanies();

        foreach ($companies['data'] ?? [] as $company) {
            $io->writeln(sprintf(
                'ID: %s | Nom : %s | Email: %s',
                $company['id'] ?? '',
                $company['name'] ?? '',
                $company['email'] ?? ''
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