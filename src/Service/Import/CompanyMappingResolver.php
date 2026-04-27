<?php

namespace App\Service\Import;

use App\Service\Normalizer\Normalizer;

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
        private Normalizer $normalizer,
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
                $this->mappingByEmail[$this->normalizer->normalizeEmail($email)] = (int) $id;
            }

            if ($name !== null) {
                $this->mappingByName[$this->normalizer->normalizeName($name)] = (int) $id;
            }
        }
    }

    public function resolve(?string $email, ?string $name): ?int
    {
        // 1. email (prioritaire)
        if ($email !== null) {
            $normalizedEmail = $this->normalizer->normalizeEmail($email);

            if (isset($this->mappingByEmail[$normalizedEmail])) {
                return $this->mappingByEmail[$normalizedEmail];
            }
        }

        // 2. fallback nom
        if ($name !== null) {
            $normalizedName = $this->normalizer->normalizeName($name);

            if (isset($this->mappingByName[$normalizedName])) {
                return $this->mappingByName[$normalizedName];
            }
        }

        return null;
    }
}