<?php

namespace App\Sellsy\Supplier;

use App\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsySupplierService
{

    private const CACHE_KEY = 'sellsy_supplier';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getSupplier(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Supplier.getList',
                'params' => [],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('[Sellsy] Fournisseurs Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('[Sellsy] Erreur récupération des forunisseurs Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }
}