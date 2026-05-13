<?php

namespace App\Sellsy\Individual;

use App\Sellsy\SellsyV2Client;
use App\Sellsy\SmartTags\SellsySmartTagsService;

final class SellsyIndividualService
{
    public function __construct(
        private SellsyV2Client $sellsyV2Client,
        private SellsySmartTagsService $SellsySmartTagsService,
    ) {
    }

    public function individualExistsByEmail(string $email): bool
    {
        $response = $this->sellsyV2Client->request('POST', '/individuals/search', [
            'filters' => [
                'email' => $email,
            ],
        ]);

        return !empty($response['data']);
    }

    public function createIndividualProspectFromGameContest(string $email): array
    {
        return $this->sellsyV2Client->request('POST', '/individuals', [
            'type' => 'prospect',
            'first_name' => 'Jeu',
            'last_name' => 'Concours',
            'email' => $email,
            'note' => "Prospect créer via le formulaire du jeu concours SHOGGA (des salons)",
            "marketing_campaigns_subscriptions" => [
                "email",
            ],
        ]);
    }

    public function linkSmartTagToIndividual(int $id): array
    {
        return $this->sellsyV2Client->request('POST', 'individuals/'.$id.'/smart-tags', [
            'value' => 'jeu concours',
        ]);
    }
}