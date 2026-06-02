<?php

namespace App\Import\AxonautToSellsy;

use App\Import\AxonautToSellsy\Factory\NormalizedInvoiceLineDtoFactory;
use App\Import\AxonautToSellsy\Mapper\SellsyV1InvoiceImportPayloadMapper;
use App\Sellsy\SellsyV1Client;
use App\Import\AxonautToSellsy\Resolver\CompanyMappingResolver;
use App\Sellsy\Staff\SellsyStaffMappingResolver;
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
                $this->logger->error('[Sellsy][Import] Erreur pré-payload', [
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
            $this->missingClientsLogger->info('[Sellsy][Import] Client manquant', [
                'invoice_number' => $invoiceNumber,
                'customer_name' => $first->customerName,
                'customer_email' => $first->customerEmail,
            ]);

            return false;
        }

        $payload = $this->mapper->map($lines, $thirdId, $staffId);

        try {
            $response = $this->client->call($payload);

            $this->logger->info('[Sellsy][Import] Réponse Sellsy V1 OK', [
                'invoice_number' => $invoiceNumber,
                'response' => $response,
            ]);

            $this->validateInvoice($response['doc_id'], $first->invoiceDate);

            $this->logger->info('[Sellsy][Import] Réponse Sellsy V1 OK', [
                'invoice_number' => $invoiceNumber,
                'response' => $response,
            ]);

            $this->createInvoicePayment($response['doc_id'], $lines);

            $this->logger->info('[Sellsy][Import] Réponse Sellsy V1 OK', [
                'invoice_number' => $invoiceNumber,
                'response' => $response,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logger->info('[Sellsy][Import] erreur facture', [
                'invoice_number' => $invoiceNumber,
                'response' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function validateInvoice(int|string $docId, string $invoiceDate): array
    {
        $date = \DateTimeImmutable::createFromFormat('d/m/Y', $invoiceDate);

        if (!$date) {
            throw new \RuntimeException(sprintf(
                'Date d emission de la facture invalide : %s',
                $invoiceDate
            ));
        }

        return $this->client->call([
            'method' => 'Document.validate',
            'params' => [
                'docid' => (string) $docId,
                'date' => $date->getTimestamp(),
            ],
        ]);
    }

    private function createInvoicePayment(string $docId,array $lines)
    {
        $payload = $this->mapper->mapPayment($docId, $lines);

        return $this->client->call($payload);
    }
}