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
            $email = $row['customer_email'] ?? null;
            $name = $row['customer_name'] ?? null;
            $id = $row['thirdid'] ?? null;

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
        // 1. nom (prioritaire)
        if ($name !== null) {
            $normalizedName = $this->normalizer->normalizeName($name);

            if (isset($this->mappingByName[$normalizedName])) {
                return $this->mappingByName[$normalizedName];
            }
        }

        // 2. fallback email
        if ($email !== null) {
            $normalizedEmail = $this->normalizer->normalizeEmail($email);

            if (isset($this->mappingByEmail[$normalizedEmail])) {
                return $this->mappingByEmail[$normalizedEmail];
            }
        }

        return null;
    }
}