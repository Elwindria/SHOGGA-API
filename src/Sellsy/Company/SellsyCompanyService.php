<?php

namespace App\Sellsy\Company;

use App\Sellsy\SellsyV2Client;
use Psr\Log\LoggerInterface;

final class SellsyCompanyService
{
    public function __construct(
        private SellsyV2Client $client,
        private LoggerInterface $logger,
    ) {
    }

    public function searchCompanyByEmail(string $email): array
    {
        try {
            $response = $this->client->request('POST', '/companies/search', [
                'filters' => [
                    'email' => $email,
                    'is_archived' => false,
                ],
            ]);

            $this->logger->info('Recherche compagnie Sellsy V2 par email', [
                'email' => $email,
            ]);

            return $response;
        } catch (\Throwable $e) {
            $this->logger->error('Erreur recherche compagnie Sellsy V2', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function companyExistsByEmail(string $email): bool
    {
        $response = $this->searchCompanyByEmail($email);

        return count($response['data'] ?? []) > 0;
    }

    public function getCompanyIdByEmail(string $email): ?int
    {
        $response = $this->searchCompanyByEmail($email);

        $company = $response['data'][0] ?? null;

        if (!is_array($company)) {
            return null;
        }

        return isset($company['id']) ? (int) $company['id'] : null;
    }
}