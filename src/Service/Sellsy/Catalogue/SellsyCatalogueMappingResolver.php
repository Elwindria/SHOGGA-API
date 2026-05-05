<?php

namespace App\Service\Sellsy\Catalogue;

use App\Service\Sellsy\Catalogue\SellsyCatalogueService;

final class SellsyCatalogueMappingResolver
{
    public function __construct(
        private SellsyCatalogueService $sellsyCatalogueService
    ) {
    }

    public function getCatalogueIdByName(string $name): int
    {
        $catalogueIds = $this->sellsyCatalogueService->getCatalogueIdsByName();

        dump($catalogueIds, $name);

        if (!isset($catalogueIds[$name])) {
            throw new \RuntimeException(sprintf(
                'Aucun produit Sellsy configuré pour le nom %s.',
                $name
            ));
        }

        return $catalogueIds[$name];
    }
}