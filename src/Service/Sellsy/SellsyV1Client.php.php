<?php

namespace App\Service\Sellsy;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SellsyV1Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly SellsyTokenManager $tokenManager,
        private string $baseUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function call(array $payload): array
    {
        $token = $this->tokenManager->getAccessToken();

        // Selon la V1, on envoie généralement le JSON encodé dans un champ (ex: 'request')
        $response = $this->httpClient->request('POST', $this->baseUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'body' => [
                'request' => json_encode($payload, JSON_THROW_ON_ERROR),
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

        // Structure typique V1 : status + response + error
        if (($data['status'] ?? '') !== 'success') {
            $error = $data['error']['message'] ?? 'Erreur inconnue Sellsy V1';
            throw new \RuntimeException($error);
        }

        return $data['response'] ?? [];
    }
}