<?php

namespace App\Sellsy\Payment;

use App\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyPaymentService
{

    private const CACHE_KEY = 'sellsy_payments';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getPayments(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Payments.getList',
                'params' => [],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('Moyen de payment Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('Erreur récupération des moyen de payment Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function getPaymentIdsByName(): array
    {
        return $this->mapPaymentsById($this->getPayments());
    }

    /**
     * @param array<mixed> $payments
     * @return array<string, int>
     */
    private function mapPaymentsById(array $payments): array
    {
        $formatted = [];

        foreach ($payments['result'] ?? [] as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $mediumName = $payment['mediumTxt'] ?? null;
            $mediumId = $payment['mediumid'] ?? null;

            if ($mediumName === null || $mediumId === null) {
                continue;
            }

            $formatted[mb_strtolower(trim($mediumName))] = (int) $mediumId;
        }

        return $formatted;
    }
}