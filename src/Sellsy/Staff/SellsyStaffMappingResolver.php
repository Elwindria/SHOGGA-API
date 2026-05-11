<?php

namespace App\Sellsy\Staff;

use App\Sellsy\Staff\SellsyStaffService;

final class SellsyStaffMappingResolver
{
    public function __construct(
        private SellsyStaffService $sellsyStaffService
    ) {
    }

    public function getStaffIdofPierreMigard(): int
    {
        $staffIds = $this->sellsyStaffService->getStaffIdsByName();

        return $staffIds['Pierre MIGARD']
            ?? $staffIds['Pierre Lopez']
            ?? throw new \RuntimeException('Aucun staff trouvé');
    }
}