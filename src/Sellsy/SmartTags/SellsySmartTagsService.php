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

    public function getSmartTagsByName(string $q): array
    {
        try {
            $response = $this->sellsyV2Client->request(
                'GET',
                '/smart-tags/individual/autocomplete',
                [
                    'q' => $q,
                ]
            );

            return $response['data'] ?? [];
        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération des SmartTags Sellsy V2', [
                'q' => $q,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    //trouve l'id d'un smart tag en fonction de son noms (seulement pour les particulier ! Sellsy fait la différence des smarttags client/particulier/société etc)
    public function getSmartTagsIdOfIndividualsByName(string $name): ?int
    {
        $tags = $this->getSmartTagsByName($name);

        foreach ($tags as $tag) {
            if (($tag['value'] ?? null) === $name) {
                return $tag['id'];
            }
        }

        return null;
    }
}