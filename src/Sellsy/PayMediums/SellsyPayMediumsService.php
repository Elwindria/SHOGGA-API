<?php

namespace App\Sellsy\PayMediums;

use App\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyPayMediumsService
{

    private const CACHE_KEY = 'sellsy_payMediums';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getPayMediums(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Accountdatas.getPayMediums',
                'params' => [],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('[Sellsy] Moyen de payment Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('[Sellsy] Erreur récupération des moyen de payment Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function getPayMediumIdsByName(): array
    {
        return $this->mapPayMediumsById($this->getPayMediums());
    }

    /**
     * @param array<mixed> $payMediums
     * @return array<string, int>
     */
    private function mapPayMediumsById(array $payMediums): array
    {
        $formatted = [];

        foreach ($payMediums as $payMedium) {
            if (!is_array($payMedium)) {
                continue;
            }

            $mediumName = $payMedium['value'] ?? null;
            $mediumId = $payMedium['id'] ?? null;

            if ($mediumName === null || $mediumId === null) {
                continue;
            }

            $formatted[mb_strtolower(trim($mediumName))] = (int) $mediumId;
        }

        return $formatted;
    }
}