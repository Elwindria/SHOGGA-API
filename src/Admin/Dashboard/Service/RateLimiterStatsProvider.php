<?php

namespace App\Admin\Dashboard\Service;

use App\Admin\Dashboard\DTO\RateLimiterStats;

final class RateLimiterStatsProvider
{
    public function __construct(
        private readonly string $logsDir,
    ) {
    }

    public function getStats(): RateLimiterStats
    {
        $path = $this->logsDir . '/prod*.log';

        if (!is_file($path)) {
            return new RateLimiterStats(0, null, null, null);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return new RateLimiterStats(0, null, null, null);
        }

        $blockedCount = 0;
        $lastBlockedIp = null;
        $lastBlockedPath = null;
        $lastBlockedDate = null;

        foreach (array_reverse($lines) as $line) {
            if (!str_contains($line, '[RateLimiter]')) {
                continue;
            }

            $blockedCount++;

            if ($lastBlockedDate === null && preg_match('/^\[(.*?)\]/', $line, $matches)) {
                $lastBlockedDate = $matches[1];
            }

            if ($lastBlockedIp === null && preg_match('/"ip":"([^"]+)"/', $line, $matches)) {
                $lastBlockedIp = $matches[1];
            }

            if ($lastBlockedPath === null && preg_match('/"path":"([^"]+)"/', $line, $matches)) {
                $lastBlockedPath = $matches[1];
            }
        }

        return new RateLimiterStats(
            blockedCount: $blockedCount,
            lastBlockedIp: $lastBlockedIp,
            lastBlockedPath: $lastBlockedPath,
            lastBlockedDate: $lastBlockedDate,
        );
    }
}