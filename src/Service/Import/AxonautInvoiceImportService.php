<?php

namespace App\Service\Import;

use App\Factory\Import\AxonautInvoiceDtoFactory;
use App\Service\Import\CompanyMappingResolver;
use App\Mapper\Import\AxonautInvoiceToSellsyPayloadMapper;
use App\Service\Sellsy\SellsyClient;

final class AxonautInvoiceImportService
{
    public function __construct(
        private readonly CsvReaderService $csvReaderService,
        private readonly AxonautInvoiceDtoFactory $dtoFactory,
        private readonly AxonautInvoiceToSellsyPayloadMapper $mapper,
        private readonly SellsyClient $sellsyClient,
        private readonly CompanyMappingResolver $companyMappingResolver,
    ) {
    }

    public function import(string $invoiceFilename, string $companyMappingFilename): int
    {
        $this->companyMappingResolver->loadFromProjectTemp($companyMappingFilename);

        $rows = $this->csvReaderService->readFromProjectTemp($invoiceFilename);

        $count = 0;

        foreach ($rows as $row) {
            $dto = $this->dtoFactory->createFromArray($row);

            if ($dto->customerName === null) {
                throw new \RuntimeException('Nom client manquant.');
            }

            $sellsyCompanyId = $this->companyMappingResolver->resolve(
                $dto->customerEmail,
                $dto->customerName
            );

            if ($sellsyCompanyId === null) {
                throw new \RuntimeException(sprintf(
                    'Aucune correspondance Sellsy trouvée pour "%s".',
                    $dto->customerName
                ));
            }

            $payload = $this->mapper->map(
                $dto,
                sellsyCompanyId: $sellsyCompanyId,
                sellsyTaxId: 1,
            );

            $this->sellsyClient->createInvoice($payload);

            ++$count;
        }

        return $count;
    }
}