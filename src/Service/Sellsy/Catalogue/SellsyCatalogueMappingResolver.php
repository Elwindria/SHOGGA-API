<?php

namespace App\Service\Sellsy\Catalogue;

use App\Service\Sellsy\Catalogue\SellsyCatalogueService;

final class SellsyCatalogueMappingResolver
{

    private const AXONAUT_TO_SELLSY_PRODUCT_NAMES = [
        'SHOGGA N°1 Origine / carton de 500 ml x 6 bouteilles – L’équilibre parfait entre plaisir & praticité'
            => 'SHOGGA N°1 Origine  (500 ml)  – L’équilibre parfait entre plaisir & praticité',

        'SHOGGA N°1 Origine / carton de 700 ml x 6 bouteilles – Le format généreux pour les convaincus'
            => 'SHOGGA N°1 Origine  (700 ml)  – Le format généreux pour les convaincus',

        'SHOGGA N°1 Origine / carton de 200 ml x 12 bouteilles – Format découverte idéal pour vos clients'
            => 'SHOGGA N°1 Origine (200 ml) – Format découverte idéal pour vos clients',

        'SHOGGA (200 ml x 12) - Boisson au gingembre premium bio'
            => 'SHOGGA N°1 Origine (200 ml) – Format découverte idéal pour vos clients',

        'Nouveau - SHOGGA N°2 Primal / carton de 500 ml x 6 bouteilles – Sans sucre ajouté'
            => 'Nouveauté - SHOGGA N°2 Primal (500 ml)  – Sans sucre ajouté',

        'Doseur/bec verseur en acier inoxydable'
            => 'Doseur/bec verseur en acier inoxydable',
    ];

    public function __construct(
        private SellsyCatalogueService $sellsyCatalogueService
    ) {
    }

    public function getCatalogueIdByInvoiceLineName(string $invoiceLineName): int
    {
        $catalogueIds = $this->sellsyCatalogueService->getCatalogueIdsByName();

        $sellsyProductName = self::AXONAUT_TO_SELLSY_PRODUCT_NAMES[$invoiceLineName] ?? null;

        if ($sellsyProductName === null) {
            throw new \RuntimeException(sprintf(
                'Aucun mapping produit Axonaut → Sellsy configuré pour "%s".',
                $invoiceLineName
            ));
        }

        if (!isset($catalogueIds[$sellsyProductName])) {
            throw new \RuntimeException(sprintf(
                'Produit Sellsy "%s" introuvable dans le catalogue.',
                $sellsyProductName
            ));
        }

        return $catalogueIds[$sellsyProductName];
    }

    public function getPurchaseAmountByInvoiceLineName(string $invoiceLineName): int
    {
        $sellsyProductName = self::AXONAUT_TO_SELLSY_PRODUCT_NAMES[$invoiceLineName] ?? null;

        if ($sellsyProductName === null) {
            throw new \RuntimeException(sprintf(
                'Aucun mapping produit Axonaut → Sellsy configuré pour "%s".',
                $invoiceLineName
            ));
        }

        $purchaseAmount = [
            'SHOGGA N°1 Origine  (500 ml)  – L’équilibre parfait entre plaisir & praticité' => 36,
            'SHOGGA N°1 Origine  (700 ml)  – Le format généreux pour les convaincus' => 48,
            'SHOGGA N°1 Origine (200 ml) – Format découverte idéal pour vos clients' => 48,
            'Nouveauté - SHOGGA N°2 Primal (500 ml)  – Sans sucre ajouté' => 38,
            'Doseur/bec verseur en acier inoxydable' => 0.35,
        ];

        return $purchaseAmount[$sellsyProductName];
    }
}