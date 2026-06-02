<?php

namespace App\Sellsy;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SellsyV2Client
{
    private float $lastRequestAt = 0.0;

    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly SellsyTokenManager $tokenManager,
        private LoggerInterface $logger,
        private string $baseUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function request(
        string $method,
        string $endpoint,
        array $payload = [],
    ): array {

        $this->throttle();

        $token = $this->tokenManager->getAccessToken();

        $response = $this->httpClient->request(
            $method,
            rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );

        $status = $response->getStatusCode();

        $content = $response->getContent(false);

        $data = json_decode($content, true);

        if ($status >= 400) {

            $this->logger->error('[Sellsy] Erreur Sellsy V2', [
                'method' => $method,
                'endpoint' => $endpoint,
                'payload' => $payload,
                'status' => $status,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf(
                'Sellsy V2 HTTP error %d',
                $status
            ));
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Réponse Sellsy V2 invalide.');
        }

        return $data;
    }

    private function throttle(): void
    {
        $minInterval = 0.25;

        $now = microtime(true);

        $elapsed = $now - $this->lastRequestAt;

        if ($elapsed < $minInterval) {
            usleep((int) (($minInterval - $elapsed) * 1_000_000));
        }

        $this->lastRequestAt = microtime(true);
    }
}