<?php

namespace App\Service\Import;

use App\Factory\Import\NormalizedInvoiceLineDtoFactory;
use App\Mapper\Import\SellsyV1PayloadMapper;
use App\Service\Sellsy\SellsyV1Client;
use App\Service\Import\CompanyMappingResolver;
use Psr\Log\LoggerInterface;

final class SellsyV1InvoiceImportService
{
    public function __construct(
        private NormalizedInvoiceLineDtoFactory $factory,
        private SellsyV1PayloadMapper $mapper,
        private SellsyV1Client $client,
        private CompanyMappingResolver $companyResolver,
        private LoggerInterface $logger,
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
        $countError = 0;

        foreach ($grouped as $invoiceNumber => $lines) {
            try {
                $this->processInvoice($invoiceNumber, $lines);
                $count++;
            }catch (\Throwable $e) {
                $this->logger->error('Erreur pré-payload n°'.$countError, [
                    'Facture n°' => $invoiceNumber,
                    'message' => $e->getMessage(),
                ]);

                $countError++;
            }
        }

        $this->logger->error("Nombre de factures réussi", [
            'Réussi ' => $count,
            'Erreur ' => $countError,
        ]);

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

        $thirdId = $this->companyResolver->resolve(
            $first->customerEmail,
            $first->customerName
        );

        $payload = $this->mapper->map($lines, $thirdId);

        try {
            $response = $this->client->call($payload);

            $this->logger->info('Réponse Sellsy V1 OK', [
                'invoice_number' => $invoiceNumber,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur Sellsy V1', [
                'invoice_number' => $invoiceNumber,
                'customer_name' => $first->customerName,
                'customer_email' => $first->customerEmail,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
        }
    }
}