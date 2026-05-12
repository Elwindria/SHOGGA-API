<?php

namespace App\Import\AxonautToSellsy\Factory;

use App\Import\AxonautToSellsy\DTO\NormalizedInvoiceLineDto;

final class NormalizedInvoiceLineDtoFactory
{
    /**
     * @param array<string, string|null> $row
     */
    public function fromRow(array $row): NormalizedInvoiceLineDto
    {
        return new NormalizedInvoiceLineDto(
            invoiceNumber: $this->string($row, 'invoice_number'),
            invoiceType: $this->string($row, 'invoice_type'),
            invoiceDate: $this->string($row, 'invoice_date'),
            paidDate: $this->string($row, 'paid_date'),
            customerName: $this->string($row, 'customer_name'),
            customerEmail: $this->nullableString($row, 'customer_email'),
            paymentMethod: $this->string($row, 'payment_method'),
            invoiceRemainingTtc: $this->float($row, 'invoice_remaining_ttc'),
            invoiceTotalTax: (float) $row['invoice_total_tax'],
            invoiceTotalHt: (float) $row['invoice_total_ht'],
            invoiceTotalTtc: (float) $row['invoice_total_ttc'],
            lineLabel: $this->string($row, 'line_label'),
            lineQuantity: $this->float($row, 'line_quantity'),
            lineUnitHt: $this->float($row, 'line_unit_ht'),
            lineUnitTtc: $this->float($row, 'line_unit_ttc'),
            lineTaxRate: $this->float($row, 'line_tax_rate'),
            billingPostalCode: $this->string($row, 'billing_postal_code'),
            billingCountry: $this->string($row, 'billing_country'),
            billingCity: $this->string($row, 'billing_city'),
            billingStreet: $this->string($row, 'billing_street'),
            lineDiscount: $this->float($row, 'line_discount'),
        );
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     * @return array<int, NormalizedInvoiceLineDto>
     */
    public function fromRows(array $rows): array
    {
        return array_map(fn (array $row) => $this->fromRow($row), $rows);
    }

    /**
     * @param array<string, string|null> $row
     */
    private function string(array $row, string $key): string
    {
        return trim((string) ($row[$key] ?? ''));
    }

    /**
     * @param array<string, string|null> $row
     */
    private function nullableString(array $row, string $key): ?string
    {
        $value = $this->string($row, $key);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, string|null> $row
     */
    private function float(array $row, string $key): float
    {
        $value = str_replace(',', '.', $this->string($row, $key));

        return (float) $value;
    }
}