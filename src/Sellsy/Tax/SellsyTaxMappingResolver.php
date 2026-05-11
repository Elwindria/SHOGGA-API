<?php

namespace App\Sellsy\Tax;

use App\Sellsy\Tax\SellsyTaxService;

final class SellsyTaxMappingResolver
{
    public function __construct(
        private SellsyTaxService $sellsyTaxService
    ) {
    }

    public function getTaxIdByRate(float $taxRate): int
    {
        $taxIds = $this->sellsyTaxService->getTaxIdsByName();

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