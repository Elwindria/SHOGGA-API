<?php

namespace App\Command;

use App\Import\AxonautToSellsy\Reader\CsvReaderService;
use App\Import\AxonautToSellsy\SellsyV1InvoiceImportService;
use App\Import\AxonautToSellsy\Resolver\CompanyMappingResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:import-axonaut-invoices',
    description: 'Importe les factures Axonaut normalisées vers Sellsy V1',
)]
class ImportAxonautInvoicesCommand extends Command
{
    public function __construct(
        private readonly CsvReaderService $csvReader,
        private readonly SellsyV1InvoiceImportService $importService,
        private readonly KernelInterface $kernel,
        private readonly CompanyMappingResolver $mappingResolver,
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
            )
            ->addArgument(
                'mapping',
                InputArgument::REQUIRED,
                'Nom du fichier CSV de mapping dans var/temp/'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $invoiceFilename = (string) $input->getArgument('invoices');
        $mappingFilename = (string) $input->getArgument('mapping');

        $this->mappingResolver->loadFromProjectTemp($mappingFilename);
        $invoicePath = $this->kernel->getProjectDir() . '/var/temp/' . $invoiceFilename;

        try {
            $rows = $this->csvReader->read($invoicePath);

            $count = $this->importService->import($rows);

            $io->success(sprintf(
                '%d validée(s), %d erreur(s)',
                $count['Validé'],
                $count['Erreur']
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}