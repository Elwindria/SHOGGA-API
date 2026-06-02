<?php

namespace App\Sellsy\Individual;

use App\Sellsy\SellsyV2Client;

final class SellsyIndividualService
{
    public function __construct(
        private SellsyV2Client $sellsyV2Client,
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

    public function findExpiredIndividualsFromGameContest(): array
    {
        $threeYearsAgo = new \DateTimeImmutable('-3 years');

        return $this->sellsyV2Client->request('POST', '/individuals/search', [
            'filters' => [
                'type' => 'prospect',
                'reference' => [
                    'SHOGGA_GAME_CONTEST',
                ],
                'created' => [
                    'end' => $threeYearsAgo->format(\DateTimeInterface::ATOM),
                ],
                'is_archived' => false,
            ],
        ]);
    }

    public function createIndividualProspectFromGameContest(string $email): array
    {
        return $this->sellsyV2Client->request('POST', '/individuals', [
            'type' => 'prospect',
            'first_name' => 'Jeu',
            'last_name' => 'Concours',
            'reference' => 'SHOGGA_GAME_CONTEST',
            'email' => $email,
            'note' => "Prospect créer via le formulaire du jeu concours SHOGGA (des salons)",
            "marketing_campaigns_subscriptions" => [
                "email",
            ],
        ]);
    }

    public function deleteIndividual(int $id): void
    {
        $this->sellsyV2Client->request(
            'DELETE',
            "/individuals/$id"
        );
    }

    public function linkSmartTagToIndividual(int $id): array
    {
        return $this->sellsyV2Client->request('POST', 'individuals/'.$id.'/smart-tags', [
            [
                'value' => 'Jeu concours',
            ]
        ]);
    }
}