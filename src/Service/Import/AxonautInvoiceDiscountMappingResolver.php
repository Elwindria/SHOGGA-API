<?php

namespace App\Service\Import;

final class AxonautInvoiceDiscountMappingResolver
{
    public function __construct(
        private string $projectDir
    ) {
    }

    private function readJSONData(): array
    {
        $path = $this->projectDir . '/var/temp/mapping-discount.json';

        $content = file_get_contents($path);

        $data = json_decode($content, true);

        return $data;
    }

    public function getGlobalInvoiceDiscountByNumberInvoices(): array
    {
        $data = $this->readJSONData();

        $mappingGlobalInvoiceDiscountByNumberInvoices = [];

        foreach ($data as $invoice) {
            $number = $invoice['number'];
            $discount = $invoice['discounts']['amount'];

            $mappingGlobalInvoiceDiscountByNumberInvoices[$number] = $discount;
        }

        return $data;
    }
}