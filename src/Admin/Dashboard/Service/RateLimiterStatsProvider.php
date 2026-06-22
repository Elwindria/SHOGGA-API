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
        $paths = glob($this->logsDir . '/prod*.log');

        if ($paths === false || $paths === []) {
            return new RateLimiterStats(0, null, null, null);
        }

        usort($paths, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        $blockedCount = 0;
        $lastBlockedIp = null;
        $lastBlockedPath = null;
        $lastBlockedDate = null;

        foreach ($paths as $path) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                continue;
            }

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
        }

        return new RateLimiterStats(
            blockedCount: $blockedCount,
            lastBlockedIp: $lastBlockedIp,
            lastBlockedPath: $lastBlockedPath,
            lastBlockedDate: $lastBlockedDate,
        );
    }
}