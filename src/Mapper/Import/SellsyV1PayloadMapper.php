<?php

namespace App\Mapper\Import;

use App\DTO\Import\NormalizedInvoiceLineDto;
use App\Service\Sellsy\SellsyTaxMappingResolver;

final class SellsyV1PayloadMapper
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
    public function map(array $lines, int $thirdId): array
    {
        if (count($lines) === 0) {
            throw new \RuntimeException('Aucune ligne à mapper.');
        }

        $first = $lines[0];

        return [
            'method' => 'Document.create',
            'params' => [
                'document' => $this->buildDocument($first, $thirdId),
                'row' => $this->buildRows($lines),
            ],
        ];
    }

    private function buildDocument(NormalizedInvoiceLineDto $line, int $thirdId): array
    {
        return [
            'doctype' => 'invoice',
            'thirdid' => (string) $thirdId,
            'enable_draft_number' => '0',
            'displayedDate' => (string) strtotime($line->invoiceDate),
            'ident' => $line->invoiceNumber,
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
        return [
            'row_type' => 'once',
            'row_name' => $line->lineLabel,
            'row_unit' => 'unité',
            'row_unitAmount' => $this->format($line->lineUnitHt),
            'row_taxid' => (string) $this->taxResolver->resolve($line->lineTaxRate),
            'row_qt' => $this->format($line->lineQuantity),
        ];
    }

    private function format(float $value): string
    {
        return number_format($value, 3, '.', '');
    }
}