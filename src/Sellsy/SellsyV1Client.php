<?php

namespace App\Sellsy;

use App\Sellsy\SellsyTokenManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SellsyV1Client
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
    public function call(array $payload): array
    {

        //Protection contre le spam de l'API Sellsy ! Limite de 5 requests par seconde ! Evite les 429 Too Many Requests
        $this->throttle();

        $token = $this->tokenManager->getAccessToken();

        // Selon la V1, on envoie généralement le JSON encodé dans un champ (ex: 'request')
        $response = $this->httpClient->request('POST', $this->baseUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'body' => [
                'io_mode' => 'json',
                'do_in' => json_encode($payload, JSON_THROW_ON_ERROR),
            ],
        ]);

        $status = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($status !== 200) {
            throw new \RuntimeException(sprintf(
                'Sellsy V1 HTTP error %d: %s',
                $status,
                $content
            ));
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Réponse Sellsy invalide (JSON).');
        }

        if (($data['status'] ?? '') !== 'success') {

            $this->logger->error('Réponse Sellsy V1 en erreur', [
                'payload' => $payload,
                'response' => $data,
            ]);

            $error = $data['error']['message'] ?? $data['error'] ?? 'Erreur inconnue Sellsy V1';

            throw new \RuntimeException(
                is_string($error)
                    ? $error
                    : json_encode($error, JSON_THROW_ON_ERROR)
            );
        }

        return $data['response'] ?? [];
    }

    private function throttle(): void
    {
        $minInterval = 0.25; // 4 requests / seconde (Evite le spam ! 4 request max envoyé, la limite est à 5, on garde de la marge)

        $now = microtime(true);
        $elapsed = $now - $this->lastRequestAt;

        if ($elapsed < $minInterval) {
            usleep((int) (($minInterval - $elapsed) * 1_000_000));
        }

        $this->lastRequestAt = microtime(true);
    }
}