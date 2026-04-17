<?php

namespace App\Command;

use App\Service\Import\CsvReaderService;
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
        private readonly CsvReaderService $csvReaderService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'filename',
            InputArgument::REQUIRED,
            'Nom du fichier CSV présent dans var/temp/ (ex: factures.csv)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = (string) $input->getArgument('filename');

        try {
            $rows = $this->csvReaderService->readFromProjectTemp($filename);

            $io->success(sprintf('%d ligne(s) lue(s) dans %s', count($rows), $filename));

            if (!empty($rows)) {
                $io->section('Exemple de première ligne');
                $io->writeln(json_encode($rows[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}