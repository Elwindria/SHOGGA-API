<?php

namespace App\Service\Import;

final class TaxResolver
{
    /**
     * @var array<string, int>
     */
    private array $taxMap = [
        '20.00' => 5680914,
        '10.00' => 5680915,
        '8.50' => 5680916,
        '5.50' => 5680917,
        '2.10' => 5680918,
    ];

    public function resolveFromRate(float $rate): int
    {
        $normalizedRate = number_format($rate, 2, '.', '');

        if (!isset($this->taxMap[$normalizedRate])) {
            throw new \RuntimeException(sprintf('Aucune taxe Sellsy trouvée pour le taux %s%%', $normalizedRate));
        }

        return $this->taxMap[$normalizedRate];
    }
}