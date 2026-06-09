<?php

namespace App\Admin\Log\Service;

use App\Admin\Log\DTO\LogEntry;
use App\Admin\Log\DTO\LogFilter;

final class LogReader
{
    private const CURRENT_FILE_KEY = 'current';

    private const PRESETS = [
        'sellsy' => 'Sellsy',
        'brevo' => 'Brevo',
        'game_contest' => 'GameContest',
        'import' => 'Import',
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

        foreach (glob($this->logsDir . '/prod*.log') ?: [] as $path) {
            $filename = basename($path);

            if ($filename === 'prod.log') {
                $files[self::CURRENT_FILE_KEY] = 'prod.log';
                continue;
            }

            if (preg_match('/^prod-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                $files[$matches[1]] = $filename;
            }
        }

        uksort($files, function (string $a, string $b): int {
            if ($a === self::CURRENT_FILE_KEY) {
                return -1;
            }

            if ($b === self::CURRENT_FILE_KEY) {
                return 1;
            }

            return strcmp($b, $a);
        });

        return $files;
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
        $path = $this->resolvePath($filter->fileKey);

        if ($path === null || !is_file($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return [];
        }

        $lines = array_reverse(array_slice($lines, -5000));

        $entries = array_map(
            fn (string $line) => $this->parseLine($line),
            $lines
        );

        return array_values(array_filter(
            $entries,
            fn (LogEntry $entry) => $this->matchFilters($entry, $filter)
        ));
    }

    private function resolvePath(string $fileKey): ?string
    {
        $files = $this->getAvailableFiles();

        if (!isset($files[$fileKey])) {
            return null;
        }

        return $this->logsDir . '/' . $files[$fileKey];
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
}