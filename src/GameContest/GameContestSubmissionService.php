<?php

namespace App\GameContest;

use App\Brevo\BrevoService;
use App\Sellsy\Individual\SellsyIndividualService;

final class GameContestSubmissionService
{
    public function __construct(
        private SellsyIndividualService $sellsyIndividualService,
        private BrevoService $brevoService,
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

            //Envoit le mail de Bienvenue
            $this->brevoService->sendEmailByTemplateId($payload["email"], 61);
        }
    }

    public function handleHasWon(array $payload): void
    {
        if ($payload["hasWon"]) {
            if ($payload["rewardType"] === "-10%") {
                $this->brevoService->sendEmailByTemplateId($payload["email"], 57);
            } else if ($payload["rewardType"] === "-20%") {
                $this->brevoService->sendEmailByTemplateId($payload["email"], 58);
            }
        }
    }
}