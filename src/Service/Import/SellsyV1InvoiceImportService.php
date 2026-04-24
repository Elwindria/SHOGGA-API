<?php

namespace App\Service\Import;

use App\Factory\Import\NormalizedInvoiceLineDtoFactory;
use App\Mapper\Import\SellsyV1PayloadMapper;
use App\Service\Sellsy\SellsyV1Client;
use App\Service\Import\CompanyMappingResolver;

final class SellsyV1InvoiceImportService
{
    public function __construct(
        private NormalizedInvoiceLineDtoFactory $factory,
        private SellsyV1PayloadMapper $mapper,
        private SellsyV1Client $client,
        private CompanyMappingResolver $companyResolver,
    ) {
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     */
    public function import(array $rows): int
    {
        $dtos = $this->factory->fromRows($rows);
        $grouped = $this->groupByInvoice($dtos);

        $count = 0;

        foreach ($grouped as $invoiceNumber => $lines) {
            try {
                $this->processInvoice($invoiceNumber, $lines);
                $count++;
            } catch (\Throwable $e) {
                dump('❌ Erreur facture', $invoiceNumber, $e->getMessage());
                continue;
            }
        }

        return $count;
    }

    /**
     * @param array<int, mixed> $dtos
     * @return array<string, array<int, mixed>>
     */
    private function groupByInvoice(array $dtos): array
    {
        $grouped = [];

        foreach ($dtos as $dto) {
            $grouped[$dto->invoiceNumber][] = $dto;
        }

        return $grouped;
    }

    /**
     * @param array<int, mixed> $lines
     */
    private function processInvoice(string $invoiceNumber, array $lines): void
    {
        $first = $lines[0];

        // 🔥 Résolution client Sellsy
        $thirdId = $this->companyResolver->resolve(
            $first->customerName,
            $first->customerEmail
        );

        // 🔥 Mapping payload V1
        $payload = $this->mapper->map($lines, $thirdId);

        // 🔥 Appel API
        $response = $this->client->call($payload);

        dump('Facture envoyée', $invoiceNumber, $response);
    }
}