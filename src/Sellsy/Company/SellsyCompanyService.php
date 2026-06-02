<?php

namespace App\Sellsy\Company;

use App\Sellsy\SellsyV2Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyCompanyService
{
    private const CACHE_KEY = 'sellsy_companies';

    public function __construct(
        private SellsyV2Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getCompanies(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            try {
                $response = $this->client->request('GET', '/companies', []);

                $this->logger->info('[Sellsy] Recherche compagnies Sellsy V2', []);

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('[Sellsy] Erreur recherche compagnies Sellsy V2', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function searchCompanyByEmail(string $email): array
    {
        try {
            $response = $this->client->request('POST', '/companies/search', [
                'filters' => [
                    'email' => $email,
                ],
            ]);

            $this->logger->info('[Sellsy] Recherche compagnie Sellsy V2 par email', [
                'email' => $email,
            ]);

            return $response;
        } catch (\Throwable $e) {
            $this->logger->error('[Sellsy] Erreur recherche compagnie Sellsy V2', [
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