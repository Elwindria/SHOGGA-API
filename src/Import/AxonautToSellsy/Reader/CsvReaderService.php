<?php

namespace App\Import\AxonautToSellsy\Reader;

use SplFileObject;

class CsvReaderService
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public function readFromProjectTemp(string $filename, string $delimiter = ';'): array
    {
        $projectDir = dirname(__DIR__, 4);
        $fullPath = $projectDir . '/var/temp/' . ltrim($filename, '/');

        if (!is_file($fullPath)) {
            throw new \RuntimeException(sprintf('Fichier introuvable : %s', $fullPath));
        }

        return $this->read($fullPath, $delimiter);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function read(string $path): array
    {
        $file = new SplFileObject($path);
        $file->setCsvControl(';', '"', '\\');
        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );

        $headers = null;
        $rows = [];

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            // Supprime le BOM UTF-8 éventuel sur la première cellule
            if ($index === 0 && isset($row[0]) && is_string($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            }

            if ($headers === null) {
                $headers = array_map(
                    static fn ($value) => is_string($value) ? trim($value) : '',
                    $row
                );

                continue;
            }

            // Complète les colonnes manquantes si une ligne est incomplète
            $row = array_pad($row, count($headers), null);

            $assocRow = [];
            foreach ($headers as $columnIndex => $header) {
                if ($header === '') {
                    continue;
                }

                $value = $row[$columnIndex] ?? null;

                $assocRow[$header] = is_string($value) ? trim($value) : $value;
            }

            // Ignore les lignes entièrement vides
            if ($this->isEmptyRow($assocRow)) {
                continue;
            }

            $rows[] = $assocRow;
        }

        return $rows;
    }

    /**
     * @param array<string, string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}