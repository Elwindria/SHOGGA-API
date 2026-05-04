<?php

namespace App\Service\Sellsy\Tax;

use App\Service\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyTaxService
{

    private const CACHE_KEY = 'sellsy_taxes';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getTaxes(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Accountdatas.getTaxes',
                'params' => [
                    'enabled' => 'all',
                ],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('Taxes Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('Erreur récupération taxes Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function getTaxIdsByName(): array
    {
        $taxes = $this->getTaxes();
        return $this->mapTaxesById($taxes);
    }

    /**
     * @param array<mixed> $taxes
     * @return array<string, int>
     */
    private function mapTaxesById(array $taxes): array
    {
        $formatted = [];

        foreach ($taxes as $tax) {
            if (!is_array($tax)) {
                continue;
            }

            if (($tax['isEnabled'] ?? null) !== 'Y') {
                continue;
            }

            $value = $tax['value'] ?? null;
            $id = $tax['id'] ?? null;

            if ($value === null || $id === null) {
                continue;
            }

            $key = number_format((float) $value, 2, '.', '');

            $formatted[$key] = (int) $id;
        }

        return $formatted;
    }
}