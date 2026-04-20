<?php

namespace App\Mapper\Import;

use App\Dto\Import\AxonautInvoiceDto;

final class AxonautInvoiceToSellsyPayloadMapper
{
    public function map(AxonautInvoiceDto $dto, int $sellsyCompanyId, int $sellsyTaxId): array
    {
        $amountExclTax = $this->toFloat($dto->amountExclTax);
        $discountAmount = $this->toFloat($dto->discountAmount);

        $rowAmount = $amountExclTax;

        if ($discountAmount !== null && $discountAmount > 0) {
            $rowAmount -= $discountAmount;
        }

        if ($rowAmount < 0) {
            $rowAmount = 0.0;
        }

        $payload = [
            'number' => $dto->invoiceNumber,
            'date' => $this->normalizeDate($dto->invoiceDate),
            'currency' => $dto->currency ?? 'EUR',
            'subject' => $dto->invoiceTitle ?? sprintf('Facture importée %s', $dto->invoiceNumber ?? ''),
            'order_reference' => $dto->invoiceReference,
            'note' => $dto->orderComments,
            'shipping_date' => $this->normalizeDate($dto->expectedDeliveryDate),
            'related' => [
                [
                    'type' => 'company',
                    'id' => $sellsyCompanyId,
                ],
            ],
            'rows' => [
                [
                    'type' => 'single',
                    'description' => $this->buildRowDescription($dto),
                    'quantity' => 1,
                    'unit_amount' => $this->formatDecimal($rowAmount),
                    'tax_id' => $sellsyTaxId,
                ],
            ],
        ];

        $payload['payment_terms'] = $this->buildPaymentTerms();

        return $this->removeNullValues($payload);
    }

    private function buildRowDescription(AxonautInvoiceDto $dto): string
    {
        $parts = [];

        if ($dto->invoiceNumber !== null) {
            $parts[] = sprintf('Facture importée Axonaut - %s', $dto->invoiceNumber);
        } else {
            $parts[] = 'Facture importée Axonaut';
        }

        if ($dto->customerName !== null) {
            $parts[] = sprintf('Client : %s', $dto->customerName);
        }

        if ($dto->orderName !== null) {
            $parts[] = sprintf('Commande : %s', $dto->orderName);
        }

        if ($dto->projectName !== null) {
            $parts[] = sprintf('Projet : %s', $dto->projectName);
        }

        return implode(' | ', $parts);
    }

    private function normalizeDate(?string $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $date = trim($date);

        if ($date === '') {
            return null;
        }

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];

        foreach ($formats as $format) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $date);

            if ($dateTime instanceof \DateTimeImmutable) {
                return $dateTime->format('Y-m-d');
            }
        }

        return null;
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

    private function formatDecimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function removeNullValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->removeNullValues($value);
            }

            if ($data[$key] === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    private function buildPaymentTerms(): array
    {
        return [
            'settings' => [
                'type' => 'settings',
                'value' => [
                    'label' => '30 days',
                ],
            ],
        ];
    }
}