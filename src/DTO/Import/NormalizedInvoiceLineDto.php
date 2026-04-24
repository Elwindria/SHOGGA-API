<?php

namespace App\DTO\Import;

final readonly class NormalizedInvoiceLineDto
{
    public function __construct(
        public string $invoiceNumber,
        public string $invoiceType,
        public string $invoiceDate,
        public string $paidDate,
        public string $customerName,
        public ?string $customerEmail,
        public string $paymentMethod,
        public float $invoiceRemainingTtc,
        public string $lineLabel,
        public float $lineQuantity,
        public float $lineUnitHt,
        public float $lineUnitTtc,
        public float $lineTaxRate,
    ) {
    }
}