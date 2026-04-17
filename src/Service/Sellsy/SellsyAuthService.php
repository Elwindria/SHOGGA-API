<?php

namespace App\Service\Sellsy;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SellsyAuthService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $authUrl,
    ) {
    }

    public function getAccessToken(): string
    {
        $response = $this->httpClient->request('POST', $this->authUrl, [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $data = $response->toArray(false);

        if (!isset($data['access_token']) || !is_string($data['access_token'])) {
            throw new \RuntimeException('Impossible de récupérer le token Sellsy.');
        }

        return $data['access_token'];
    }
}