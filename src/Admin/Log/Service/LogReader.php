<?php

namespace App\Admin\Log\Service;

use App\Admin\Log\DTO\LogEntry;
use App\Admin\Log\DTO\LogFilter;
use App\Admin\Dashboard\DTO\LogStats;

final class LogReader
{
    private const CURRENT_FILE_KEY = 'current';

    private const PRESETS = [
        'sellsy' => 'Sellsy',
        'brevo' => 'Brevo',
        'game_contest' => 'GameContest',
        'import' => 'Import',
        'rate_limiter' => 'RateLimiter',
    ];

    public function __construct(
        private readonly string $logsDir,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableFiles(): array
    {
        $files = [];

        foreach (glob($this->logsDir . '/prod-*.log') ?: [] as $path) {
            $filename = basename($path);

            if (preg_match('/^prod-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                $files[$matches[1]] = $filename;
            }
        }

        krsort($files);

        if ($files !== []) {
            $latestKey = array_key_first($files);

            return [
                'current' => $files[$latestKey],
                'all' => 'Tous les logs',
            ] + $files;
        }

        return [];
    }

    /**
     * @return array<int, LogEntry>
     */
    public function read(LogFilter $filter): array
    {
        $entries = $this->readAllMatching($filter);

        $offset = ($filter->page - 1) * $filter->limit;

        return array_slice($entries, $offset, $filter->limit);
    }

    public function count(LogFilter $filter): int
    {
        return count($this->readAllMatching($filter));
    }

    /**
     * @return array<string, string>
     */
    public function getPresets(): array
    {
        return self::PRESETS;
    }

    /**
     * @return array<int, LogEntry>
     */
    private function readAllMatching(LogFilter $filter): array
    {
        $paths = $this->resolvePaths($filter->fileKey);

        if ($paths === []) {
            return [];
        }

        $entries = [];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $entry = $this->parseLine($line);

                if ($this->matchFilters($entry, $filter)) {
                    $entries[] = $entry;
                }
            }
        }

        $entries = array_reverse($entries);

        return array_slice($entries, 0, 5000);
    }

    private function resolvePaths(string $fileKey): array
    {
        $files = $this->getAvailableFiles();

        if ($fileKey === 'all') {
            $paths = [];

            foreach ($files as $key => $filename) {
                if ($key === 'current' || $key === 'all') {
                    continue;
                }

                $paths[] = $this->logsDir . '/' . $filename;
            }

            return $paths;
        }

        if (!isset($files[$fileKey])) {
            return [];
        }

        if ($fileKey === 'all') {
            return [];
        }

        return [
            $this->logsDir . '/' . $files[$fileKey],
        ];
    }

    private function parseLine(string $line): LogEntry
    {
        preg_match('/^\[(.*?)\]\s+\w+\.(\w+):\s+(.*)$/', $line, $matches);

        return new LogEntry(
            raw: $line,
            date: $matches[1] ?? null,
            level: isset($matches[2]) ? strtolower($matches[2]) : null,
            message: $matches[3] ?? $line,
        );
    }

    private function matchFilters(LogEntry $entry, LogFilter $filter): bool
    {
        if ($filter->level !== null && $filter->level !== '' && $entry->level !== strtolower($filter->level)) {
            return false;
        }

        if ($filter->preset !== null && $filter->preset !== '') {
            $presetValue = self::PRESETS[$filter->preset] ?? null;

            if ($presetValue !== null && !str_contains(strtolower($entry->raw), strtolower($presetValue))) {
                return false;
            }
        }

        if ($filter->search !== null && $filter->search !== '') {
            if (!str_contains(strtolower($entry->raw), strtolower($filter->search))) {
                return false;
            }
        }

        return true;
    }

    public function getCurrentLogStats(): LogStats
    {
        $filter = new LogFilter(
            fileKey: self::CURRENT_FILE_KEY,
            level: null,
            search: null,
            preset: null,
            page: 1,
            limit: 5000,
        );

        $entries = $this->read($filter);

        $errors = 0;
        $warnings = 0;
        $infos = 0;

        foreach ($entries as $entry) {
            match ($entry->level) {
                'error', 'critical', 'alert', 'emergency' => $errors++,
                'warning' => $warnings++,
                'info' => $infos++,
                default => null,
            };
        }

        return new LogStats(
            total: count($entries),
            errors: $errors,
            warnings: $warnings,
            infos: $infos,
        );
    }
}