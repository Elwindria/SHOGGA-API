<?php

namespace App\Command;

use App\Service\Import\CsvReaderService;
use App\Service\Import\SellsyV1InvoiceImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-axonaut-invoices',
    description: 'Importe les factures Axonaut normalisées vers Sellsy V1',
)]
class ImportAxonautInvoicesCommand extends Command
{
    public function __construct(
        private readonly CsvReaderService $csvReader,
        private readonly SellsyV1InvoiceImportService $importService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'invoices',
                InputArgument::REQUIRED,
                'Nom du fichier CSV normalisé des factures dans var/temp/'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $invoiceFilename = (string) $input->getArgument('invoices');
        $invoicePath = sprintf('%s/../var/temp/%s', __DIR__, $invoiceFilename);

        try {
            $rows = $this->csvReader->read($invoicePath);

            $count = $this->importService->import($rows);

            $io->success(sprintf('%d facture(s) importée(s) dans Sellsy.', $count));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}