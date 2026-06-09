<?php

namespace App\GameContest;

use App\Brevo\BrevoService;
use App\Sellsy\Individual\SellsyIndividualService;
use App\GameContest\Entity\GameContestEmailAttempt;
use Doctrine\ORM\EntityManagerInterface;

final class GameContestSubmissionService
{
    public function __construct(
        private SellsyIndividualService $sellsyIndividualService,
        private BrevoService $brevoService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(array $payload): void
    {
        //Rajout du email dans une DB temporaire pour éviter qu'un joueur puisse spam/rejouer en boucle avec le même email
        $this->registerEmail($payload["email"]);

        if ($payload["newsletter"]) {

            //Créer l'individu
            $individual = $this->sellsyIndividualService->createIndividualProspectFromGameContest($payload['email']);
            $id = $individual['id'];

            //ratache un smartTag "jeu concours"
            $this->sellsyIndividualService->linkSmartTagToIndividual($id, "Jeu concours");

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

    private function registerEmail(string $email): void
    {
        $attempt = new GameContestEmailAttempt($email);

        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
    }
}