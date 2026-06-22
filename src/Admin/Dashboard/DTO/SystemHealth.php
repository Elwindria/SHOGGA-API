<?php

namespace App\Admin\Dashboard\DTO;

final readonly class SystemHealth
{
    public function __construct(
        public bool $applicationOk,
        public bool $databaseOk,
        public bool $logsDirectoryOk,
        public ?string $lastMaintenanceDate,
    ) {
    }
}