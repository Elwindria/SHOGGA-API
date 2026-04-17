<?php

namespace App\Service\Import;

final class CompanyMappingResolver
{
    /**
     * @var array<string, int>
     */
    private array $mappingByEmail = [];

    /**
     * @var array<string, int>
     */
    private array $mappingByName = [];

    public function __construct(
        private readonly CsvReaderService $csvReaderService,
    ) {
    }

    public function loadFromProjectTemp(string $filename): void
    {
        $rows = $this->csvReaderService->readFromProjectTemp($filename);

        foreach ($rows as $row) {
            $email = $row['EMAIL SOCIETE'] ?? null;
            $name = $row['NOM SOCIETE'] ?? null;
            $id = $row['ID SOCIETE SELLSY'] ?? null;

            if ($id === null) {
                continue;
            }

            if ($email !== null) {
                $this->mappingByEmail[$this->normalizeEmail($email)] = (int) $id;
            }

            if ($name !== null) {
                $this->mappingByName[$this->normalizeName($name)] = (int) $id;
            }
        }
    }

    public function resolve(?string $email, ?string $name): ?int
    {
        // 1. email (prioritaire)
        if ($email !== null) {
            $normalizedEmail = $this->normalizeEmail($email);

            if (isset($this->mappingByEmail[$normalizedEmail])) {
                return $this->mappingByEmail[$normalizedEmail];
            }
        }

        // 2. fallback nom
        if ($name !== null) {
            $normalizedName = $this->normalizeName($name);

            if (isset($this->mappingByName[$normalizedName])) {
                return $this->mappingByName[$normalizedName];
            }
        }

        return null;
    }

    private function normalizeEmail(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }
}