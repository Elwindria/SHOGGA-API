<?php

namespace App\GameContest;

use App\Sellsy\Individual\SellsyIndividualService;

final class GameContestSubmissionService
{
    public function __construct(
        private SellsyIndividualService $sellsyIndividualService,
    ) {
    }

    public function handle(array $payload): void
    {
        if ($payload["newsletter"]) {

            //Créer l'individu
            $individual = $this->sellsyIndividualService->createIndividualProspectFromGameContest($payload['email']);
            $id = $individual['id'];

            //ratache un smartTag "jeu concours"
            $this->sellsyIndividualService->linkSmartTagToIndividual($id);
        }
    }

    public function handleHasWon(array $payload): void
    {
        if ($payload["hasWon"]) {
            if ($payload["rewardType"] === "-10%" || $payload["rewardType"] === "-20%") {
                //envoyé le mail via brevo ?
            }    
        }
    }
}