<?php

namespace App\Service\Sellsy;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class SellsyClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SellsyAuthService $authService,
        private readonly string $apiBaseUrl,
    ) {
    }

    public function createInvoice(array $payload): array
    {
        return $this->request('POST', '/invoices', [
            'json' => $payload,
        ]);
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        $token = $this->authService->getAccessToken();

        $options['headers']['Authorization'] = sprintf('Bearer %s', $token);
        $options['headers']['Accept'] = 'application/json';

        $response = $this->httpClient->request(
            $method,
            rtrim($this->apiBaseUrl, '/') . '/' . ltrim($uri, '/'),
            $options
        );

        return $this->handleResponse($response);
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        $data = json_decode($content, true);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf(
                'Erreur Sellsy [%d] : %s',
                $statusCode,
                is_string($content) ? $content : 'Réponse inconnue'
            ));
        }

        return is_array($data) ? $data : [];
    }
}