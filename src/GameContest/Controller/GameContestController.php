<?php

namespace App\GameContest\Controller\GameContest;

use App\GameContest\Validator\GameContestSubmissionValidator;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GameContestController extends AbstractController
{
    public function __construct(
        private GameContestSubmissionValidator $gameContestSubmissionValidator,
    ) {
    }

    #[Route('/api/game-contest/submit', name: 'api_game_contest_submit', methods: ['POST'])]
    public function submit(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        // Exemple payload
        // payload {
        //     "email": "test@example.com",
        //     "hasWon": true,
        //     "rewardType": "discount",
        //     "newsletter": true,
        //     "rgpd": true
        // }

        try {
            //Vérifier si Email non vide / Valide / N'existe pas déjà dans la base de donnée (donc il a déjà jouer)
            $this->gameContestSubmissionValidator->validateEmail($payload);

            //Vérifie si les RGPD sont acceptés
            $this->gameContestSubmissionValidator->validateRGPD($payload);

            

            return new JsonResponse([
                'success' => true,
            ]);

        } catch (\RuntimeException $e) {

            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}