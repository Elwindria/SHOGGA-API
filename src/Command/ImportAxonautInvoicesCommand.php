<?php

namespace App\Command;

use App\Service\Import\AxonautInvoiceImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-axonaut-invoices',
    description: 'Importe les factures Axonaut (CSV) vers Sellsy',
)]
class ImportAxonautInvoicesCommand extends Command
{
    public function __construct(
        private readonly AxonautInvoiceImportService $importService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'invoices',
                InputArgument::REQUIRED,
                'Nom du fichier CSV des factures dans var/temp/'
            )
            ->addArgument(
                'mapping',
                InputArgument::REQUIRED,
                'Nom du fichier CSV de mapping sociétés dans var/temp/'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $invoiceFilename = (string) $input->getArgument('invoices');
        $mappingFilename = (string) $input->getArgument('mapping');

        try {
            $count = $this->importService->import($invoiceFilename, $mappingFilename);

            $io->success(sprintf('%d facture(s) importée(s) dans Sellsy.', $count));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}