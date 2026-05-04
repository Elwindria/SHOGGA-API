<?php

namespace App\Service\Sellsy\Staff;

use App\Service\Sellsy\Staff\SellsyStaffService;

final class SellsyStaffMappingResolver
{
    public function __construct(
        private SellsyStaffService $sellsyStaffService
    ) {
    }

    public function getStaffIdofPierreMigard(): int
    {
        $staffIds = $this->sellsyStaffService->getStaffIdsByName();

        return $staffIds['Pierre MIGARD'];
    }
}