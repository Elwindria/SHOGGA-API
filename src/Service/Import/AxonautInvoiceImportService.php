<?php

namespace App\Service\Import;

use App\Factory\Import\AxonautInvoiceDtoFactory;
use App\Mapper\Import\AxonautInvoiceToSellsyPayloadMapper;
use App\Service\Sellsy\SellsyClient;

final class AxonautInvoiceImportService
{
    public function __construct(
        private readonly CsvReaderService $csvReaderService,
        private readonly AxonautInvoiceDtoFactory $dtoFactory,
        private readonly AxonautInvoiceToSellsyPayloadMapper $mapper,
        private readonly SellsyClient $sellsyClient,
    ) {
    }

    public function import(string $filename): int
    {
        $rows = $this->csvReaderService->readFromProjectTemp($filename);

        $count = 0;

        foreach ($rows as $row) {
            $dto = $this->dtoFactory->createFromArray($row);

            $payload = $this->mapper->map(
                $dto,
                sellsyCompanyId: 123,
                sellsyTaxId: 1,
            );

            $this->sellsyClient->createInvoice($payload);

            ++$count;
        }

        return $count;
    }
}