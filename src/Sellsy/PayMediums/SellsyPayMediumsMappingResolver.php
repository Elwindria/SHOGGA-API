<?php

namespace App\Sellsy\PayMediums;

use App\Sellsy\PayMediums\SellsyPayMediumsService;

final class SellsyPayMediumsMappingResolver
{
    public function __construct(
        private SellsyPayMediumsService $sellsyPayMediumsService
    ) {
    }

    public function getPayMediumsIdByName(string $payMediumName): int
    {
        $payMediumsIds = $this->sellsyPayMediumsService->getPayMediumIdsByName();

        $normalizedName = $this->normalizePayMediumName($payMediumName);

        if (!isset($payMediumsIds[$normalizedName])) {
            throw new \RuntimeException(sprintf(
                'Aucun moyen de paiement Sellsy configuré pour le nom "%s" normalisé en "%s".',
                $payMediumName,
                $normalizedName
            ));
        }

        return $payMediumsIds[$normalizedName];
    }

    //le nom dans le csv n'est pas tjs le même que ceux de sellsy
    private function normalizePayMediumName(string $payMediumName): string
    {
        return match (mb_strtolower(trim($payMediumName))) {
            'virement' => 'virement bancaire',
            'cb' => 'carte bancaire',
            'chèque', 'cheque' => 'chèque',
            'prélèvement', 'prelevement' => 'prélèvement',
            'espèces', 'especes', 'espèce', 'espece' => 'espèce',
            'autre' => 'virement bancaire',

            default => mb_strtolower(trim($payMediumName)),
        };
    }
}