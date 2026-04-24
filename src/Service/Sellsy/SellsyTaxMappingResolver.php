<?php

namespace App\Service\Sellsy;

final class SellsyTaxMappingResolver
{
    /**
     * À remplacer avec les vrais IDs TVA Sellsy.
     */
    private const TAX_IDS = [
        '20.00' => 5680914,
        '10.00' => 5680915,
        '8.50' => 5680916,
        '5.50' => 5680917,
        '2.10' => 5680918,
    ];

    public function resolve(float $taxRate): int
    {
        $key = number_format($taxRate, 2, '.', '');

        if (!isset(self::TAX_IDS[$key])) {
            throw new \RuntimeException(sprintf(
                'Aucun row_taxid Sellsy configuré pour le taux de TVA %s%%.',
                $key
            ));
        }

        return self::TAX_IDS[$key];
    }
}