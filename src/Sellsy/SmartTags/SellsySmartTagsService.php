<?php

namespace App\Sellsy\SmartTags;

use App\Sellsy\SellsyV2Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class SellsySmartTagsService
{

    private const CACHE_KEY = 'sellsy_smart_tags';

    public function __construct(
        private SellsyV2Client $sellsyV2Client,
        private LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getSmartTags(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            // durée de vie du cache (ex: 1 jour)
            $item->expiresAfter(86400);

            try {
                $response = $this->sellsyV2Client->request('GET', '/smart-tags/individuals/autocomplete');

                $this->logger->info('SmartTags Sellsy récupérées depuis API');

                return $response;
            } catch (\Throwable $e) {
                $this->logger->error('Erreur récupération des SmartTags Sellsy V2', [
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    //trouve l'id d'un smart tag en fonction de son noms (seulement pour les particulier ! Sellsy fait la différence des smarttags client/particulier/société etc)
    public function getSmartTagsIdOfIndividualsByName(string $name): ?int
    {
        $tags = $this->getSmartTags() ?? [];

        foreach ($tags as $tag) {
            if ($tag['value'] === $name) {
                return $tag['id'];
            }
        }

        return null;
    }
}