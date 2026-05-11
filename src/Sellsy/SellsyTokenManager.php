<?php

namespace App\Sellsy;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SellsyTokenManager
{
    private const CACHE_KEY = 'sellsy_access_token';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $authUrl,
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): string {
            $response = $this->httpClient->request('POST', $this->authUrl, [
                'body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = $response->toArray(false);

            if (!isset($data['access_token'])) {
                throw new \RuntimeException('Impossible de récupérer le token Sellsy.');
            }

            $expiresIn = (int) ($data['expires_in'] ?? 3600);

            // Petite marge pour éviter d’utiliser un token expiré
            $item->expiresAfter(max(60, $expiresIn - 60));

            return $data['access_token'];
        });
    }

    public function clearToken(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }
}