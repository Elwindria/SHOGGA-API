<?php

namespace App\Service\Import;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AxonautInvoiceDiscountMappingResolver
{
    private const CACHE_KEY = 'InvoiceDiscountByInvoiceNumber';

    public function __construct(
        private string $projectDir,
        private readonly CacheInterface $cache,
    ) {
    }

    private function readJSONData(): array
    {
        $path = $this->projectDir . '/var/temp/mapping-discount.json';

        $content = file_get_contents($path);

        $data = json_decode($content, true);

        return $data;
    }

    private function getGlobalInvoiceDiscountByInvoiceNumber(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $data = $this->readJSONData();

            $mappingGlobalInvoiceDiscountByInvoiceNumber = [];

            foreach ($data as $invoice) {
                $number = $invoice['number'] ?? null;
                $discount = $invoice['discounts']['amount'] ?? 0;

                if ($number === null) {
                    continue;
                }

                $mappingGlobalInvoiceDiscountByInvoiceNumber[$number] = $discount;
            }

            return $mappingGlobalInvoiceDiscountByInvoiceNumber;
        });
    }

    public function getGlobalDiscountByInvoiceNumber(string $invoiceNumber): float
    {
        $mapping = $this->getGlobalInvoiceDiscountByInvoiceNumber();

        //On peut retourner null car pas obligatoire dans le payload final pour que la request marche
        return isset($mapping[$invoiceNumber])
            ? (float) $mapping[$invoiceNumber]
            : null;
    }
}