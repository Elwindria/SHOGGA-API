<?php

namespace App\Admin\Dashboard\Service;

use App\Admin\Dashboard\DTO\SystemHealth;
use Doctrine\DBAL\Connection;

final class SystemHealthProvider
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $logsDir,
    ) {
    }

    public function getHealth(): SystemHealth
    {
        $databaseOk = false;

        try {
            $this->connection->executeQuery('SELECT 1');
            $databaseOk = true;
        } catch (\Throwable) {
        }

        $logsDirectoryOk = is_dir($this->logsDir) && is_readable($this->logsDir);

        return new SystemHealth(
            applicationOk: true,
            databaseOk: $databaseOk,
            logsDirectoryOk: $logsDirectoryOk,
            lastMaintenanceDate: $this->findLastMaintenanceDate(),
        );
    }

    private function findLastMaintenanceDate(): ?string
    {
        $path = $this->logsDir . '/prod.log';

        if (!is_file($path)) {
            return null;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return null;
        }

        foreach (array_reverse($lines) as $line) {
            if (!str_contains($line, '[Maintenance] Daily maintenance completed')) {
                continue;
            }

            if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}