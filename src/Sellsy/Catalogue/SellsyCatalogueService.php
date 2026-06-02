<?php

namespace App\Sellsy\Catalogue;

use App\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyCatalogueService
{

    private const CACHE_KEY = 'sellsy_catalogue';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getCatalogue(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Catalogue.getList',
                'params' => [
                    'type' => "item",
                ],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('[Sellsy] Catalogue Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('[Sellsy] Erreur récupération du Catalogue Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function getCatalogueIdsByName(): array
    {
        return $this->mapCatalogueById($this->getCatalogue());
    }

    /**
     * @param array<mixed> $catalogue
     * @return array<string, int>
     */
    private function mapCatalogueById(array $catalogue): array
    {
        $formatted = [];

            foreach ($catalogue['result'] ?? [] as $c) {
                if (!is_array($c)) {
                    continue;
                }

                $name = $c['tradename'] ?? null;
                $id = $c['id'] ?? null;

                if ($name === null || $id === null) {
                    continue;
                }

                $formatted[$name] = (int) $id;
            }

            return $formatted;
    }
}