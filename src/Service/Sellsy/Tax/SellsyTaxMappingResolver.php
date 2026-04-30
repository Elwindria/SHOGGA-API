<?php

namespace App\Service\Sellsy\Tax;

use App\Service\Sellsy\Tax\SellsyTaxService;

final class SellsyTaxMappingResolver
{
    public function __construct(
        private SellsyTaxService $sellsyTaxService
    ) {
    }

    public function resolve(float $taxRate): int
    {
        $taxIds = $this->sellsyTaxService->getTaxId();

        $key = number_format($taxRate, 2, '.', '');

        if (!isset($taxIds[$key])) {
            throw new \RuntimeException(sprintf(
                'Aucun row_taxid Sellsy configuré pour le taux de TVA %s%%.',
                $key
            ));
        }

        return $taxIds[$key];
    }
}