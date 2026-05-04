<?php

namespace App\Mapper\Import;

use App\DTO\Import\NormalizedInvoiceLineDto;
use App\Service\Sellsy\Tax\SellsyTaxMappingResolver;

final class SellsyV1InvoiceImportPayloadMapper
{
    public function __construct(
        private SellsyTaxMappingResolver $taxResolver,
    ) {
    }

    /**
     * @param array<int, NormalizedInvoiceLineDto> $lines
     * @param int $thirdId
     * @return array<string, mixed>
     */
    public function map(array $lines, int $thirdId, int $staffId): array
    {
        if (count($lines) === 0) {
            throw new \RuntimeException('Aucune ligne à mapper.');
        }

        $first = $lines[0];

        return [
            'method' => 'Document.create',
            'params' => [
                'document' => $this->buildDocument($first, $thirdId, $staffId),
                'row' => $this->buildRows($lines),
            ],
        ];
    }

    private function buildDocument(
        NormalizedInvoiceLineDto $line,
        int $thirdId,
        int $staffId
    ): array {
        return [
            'doctype' => 'invoice',
            'thirdid' => (string) $thirdId,
            'enable_draft_number' => '1',
            'displayedDate' => (string) strtotime($line->invoiceDate),
            'subject' => 'Import historique Axonaut - '.$line->invoiceNumber,
            'notes' => $this->buildNotes($line),
            'docspeakerStaffId' => $staffId,
        ];
    }

    /**
     * @param array<int, NormalizedInvoiceLineDto> $lines
     * @return array<string, array<string, string>>
     */
    private function buildRows(array $lines): array
    {
        $rows = [];

        foreach ($lines as $index => $line) {
            $rows[(string) ($index + 1)] = $this->buildRow($line);
        }

        return $rows;
    }

    private function buildRow(NormalizedInvoiceLineDto $line): array
    {
        $row = [
            'row_type' => 'once',
            'row_name' => $line->lineLabel,
            'row_unit' => 'unité',
            'row_unitAmount' => $this->format($line->lineUnitHt),
            'row_taxid' => (string) $this->taxResolver->getTaxIdByRate($line->lineTaxRate),
            'row_qt' => $this->format($line->lineQuantity),
        ];

        if ($line->lineDiscount > 0) {
            $row['row_discount'] = $this->format($line->lineDiscount);
            $row['row_discountUnit'] = 'amount';
        }

        return $row;
    }

    private function format(float $value): string
    {
        return number_format($value, 3, '.', '');
    }

    private function buildNotes(NormalizedInvoiceLineDto $line): string
    {
        return sprintf(
            "Ancien numéro de facture Axonaut : %s\nDate de paiement : %s\nMode de paiement : %s",
            $line->invoiceNumber,
            $line->paidDate,
            $line->paymentMethod
        );
    }
}