<?php

namespace App\Dto\Import;

final class AxonautInvoiceDto
{
    public function __construct(
        public readonly ?string $companyCategories,
        public readonly ?string $createdAt,
        public readonly ?string $lastContactAt,
        public readonly ?string $orderChannel,
        public readonly ?string $billingPostalCode,
        public readonly ?string $deliveryPostalCode,
        public readonly ?string $customerThirdPartyCode,
        public readonly ?string $orderComments,
        public readonly ?string $invoiceDate,
        public readonly ?string $expectedDeliveryDate,
        public readonly ?string $paymentDate,
        public readonly ?string $dueDate,
        public readonly ?string $currency,
        public readonly ?string $customerEmail,
        public readonly ?string $frequency,
        public readonly ?string $invoiceId,
        public readonly ?string $companyId,
        public readonly ?string $contactName,
        public readonly ?string $amountExclTax,
        public readonly ?string $amountInclTax,
        public readonly ?string $taxAmount,
        public readonly ?string $discountAmount,
        public readonly ?string $paymentMethod,
        public readonly ?string $orderName,
        public readonly ?string $customerName,
        public readonly ?string $projectName,
        public readonly ?string $customerVatNumber,
        public readonly ?string $invoiceNumber,
        public readonly ?string $projectNumber,
        public readonly ?string $billingCountry,
        public readonly ?string $deliveryCountry,
        public readonly ?string $invoiceReference,
        public readonly ?string $paymentReferences,
        public readonly ?string $billingTheme,
        public readonly ?string $invoiceTitle,
        public readonly ?string $invoiceType,
        public readonly ?string $billingCity,
        public readonly ?string $deliveryCity,
    ) {
    }
}