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
        private readonly CompanyMappingResolver $companyMappingResolver,
        private readonly TaxResolver $taxResolver,
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

            $taxRate = $this->computeTaxRate($dto->amountExclTax, $dto->taxAmount);
            $sellsyTaxId = $this->taxResolver->resolveFromRate($taxRate);

            $payload = $this->mapper->map(
                $dto,
                sellsyCompanyId: $sellsyCompanyId,
                sellsyTaxId: $sellsyTaxId,
            );

            $this->sellsyClient->createInvoice($payload);

            ++$count;
        }

        return $count;
    }

    private function computeTaxRate(?string $amountExclTax, ?string $taxAmount): float
    {
        $ht = $this->toFloat($amountExclTax);
        $tva = $this->toFloat($taxAmount);

        if ($ht === null || $tva === null || $ht <= 0) {
            throw new \RuntimeException('Impossible de calculer le taux de TVA.');
        }

        return round(($tva / $ht) * 100, 2);
    }

    private function toFloat(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim($value));

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}