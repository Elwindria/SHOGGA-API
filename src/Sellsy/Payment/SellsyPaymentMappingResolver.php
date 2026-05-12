<?php

namespace App\Sellsy\Payment;

use App\Sellsy\Payment\SellsyPaymentService;

final class SellsyPaymentMappingResolver
{
    public function __construct(
        private SellsyPaymentService $sellsyPaymentService
    ) {
    }

    public function getPaymentIdByName(string $paymentMethod): int
    {
        $payments = $this->sellsyPaymentService->getPaymentIdsByName();

        $normalized = mb_strtolower(trim($paymentMethod));

        if (!isset($payments[$normalized])) {
            throw new \RuntimeException(sprintf(
                'Moyen de paiement Sellsy introuvable : %s',
                $paymentMethod
            ));
        }

        return $payments[$normalized];
    }
}