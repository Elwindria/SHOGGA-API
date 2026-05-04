<?php

namespace App\Service\Import;

use App\Factory\Import\NormalizedInvoiceLineDtoFactory;
use App\Mapper\Import\SellsyV1InvoiceImportPayloadMapper;
use App\Service\Sellsy\SellsyV1Client;
use App\Service\Import\CompanyMappingResolver;
use App\Service\Sellsy\Staff\SellsyStaffMappingResolver;
use Psr\Log\LoggerInterface;

final class SellsyV1InvoiceImportService
{
    public function __construct(
        private NormalizedInvoiceLineDtoFactory $factory,
        private SellsyV1InvoiceImportPayloadMapper $mapper,
        private SellsyV1Client $client,
        private CompanyMappingResolver $companyResolver,
        private LoggerInterface $logger,
        private readonly LoggerInterface $missingClientsLogger,
        private SellsyStaffMappingResolver $sellsyStaffMappingResolver,
    ) {
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     */
    public function import(array $rows): array
    {
        $dtos = $this->factory->fromRows($rows);
        $grouped = $this->groupByInvoice($dtos);
        $count = [
            "Validé" => 0,
            "Erreur" => 0,
        ];

        foreach ($grouped as $invoiceNumber => $lines) {
            try {
                if ($this->processInvoice($invoiceNumber, $lines)) {
                    $count["Validé"]++;
                } else {
                    $count["Erreur"]++;
                };
            }catch (\Throwable $e) {
                $this->logger->error('Erreur pré-payload', [
                    'Facture n°' => $invoiceNumber,
                    'message' => $e->getMessage(),
                ]);

                $count["Erreur"]++;
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
    private function processInvoice(string $invoiceNumber, array $lines): bool
    {
        $first = $lines[0];

        $thirdId = $this->companyResolver->getThirdIdByEmailOrName(
            $first->customerEmail,
            $first->customerName
        );

        $staffId = $this->sellsyStaffMappingResolver->getStaffIdofPierreMigard();

        if ($thirdId === null) {
            $this->missingClientsLogger->info('Client manquant', [
                'invoice_number' => $invoiceNumber,
                'customer_name' => $first->customerName,
                'customer_email' => $first->customerEmail,
            ]);

            return false;
        }

        $payload = $this->mapper->map($lines, $thirdId, $staffId);

        try {
            $response = $this->client->call($payload);

            $this->logger->info('Réponse Sellsy V1 OK', [
                'invoice_number' => $invoiceNumber,
                'response' => $response,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Erreur Sellsy V1', [
                'invoice_number' => $invoiceNumber,
                'customer_name' => $first->customerName,
                'customer_email' => $first->customerEmail,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}