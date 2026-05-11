<?php

namespace App\Sellsy\Staff;

use App\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsyStaffService
{

    private const CACHE_KEY = 'sellsy_staffs';

    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getStaffs(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            $payload = [
                'method' => 'Staffs.getList',
                'params' => [],
            ];

            try {
                $response = $this->client->call($payload);

                $this->logger->info('Collaborateurs Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('Erreur récupération des collaborateurs Sellsy V1', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function getStaffIdsByName(): array
    {
        return $this->mapStaffsById($this->getStaffs());
    }

    /**
     * @param array<mixed> $staffs
     * @return array<string, int>
     */
    private function mapStaffsById(array $staffs): array
    {
        $formatted = [];

        foreach ($staffs['result'] ?? [] as $staff) {
            if (!is_array($staff)) {
                continue;
            }

            $fullName = $staff['fullName'] ?? null;
            $id = $staff['linkedid'] ?? null;

            if ($fullName === null || $id === null) {
                continue;
            }

            $formatted[$fullName] = (int) $id;
        }

        return $formatted;
    }
}