<?php

namespace App\Service\Sellsy\Tax;

use App\Service\Sellsy\SellsyV1Client;
use Psr\Log\LoggerInterface;

final class SellsyTaxService
{
    public function __construct(
        private SellsyV1Client $client,
        private LoggerInterface $logger,
    ) {
    }

    public function getTaxId(): array
    {

        $payload = [
            'method' =>  'Accountdatas.getTaxes',
            'params' => [
                'enabled' => "all",
            ] 
        ];

        try {
            $response = $this->client->call($payload);

            return $this->formatTaxes($response);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération taxes Sellsy V1', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<mixed> $taxes
     * @return array<string, int>
     */
    private function formatTaxes(array $taxes): array
    {
        $formatted = [];

        foreach ($taxes as $tax) {
            if (!is_array($tax)) {
                continue;
            }

            if (($tax['isEnabled'] ?? null) !== 'Y') {
                continue;
            }

            $value = $tax['value'] ?? null;
            $id = $tax['id'] ?? null;

            if ($value === null || $id === null) {
                continue;
            }

            $key = number_format((float) $value, 2, '.', '');

            $formatted[$key] = (int) $id;
        }

        return $formatted;
    }
}