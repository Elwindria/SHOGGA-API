<?php

namespace App\GameContest\Controller;

use App\GameContest\GameContestSubmissionService;
use App\GameContest\Validator\GameContestSubmissionValidator;
use App\Shared\Normalizer\Normalizer;
use App\Shared\Sanitizer\Sanitizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GameContestController extends AbstractController
{
    public function __construct(
        private GameContestSubmissionValidator $gameContestSubmissionValidator,
        private Sanitizer $sanitizer,
        private Normalizer $normalizer,
        private GameContestSubmissionService $gameContestSubmissionService,
    ) {
    }

    #[Route('/api/game-contest/submit', name: 'api_game_contest_submit', methods: ['POST'])]
    public function submit(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        //normalize + sanitize email
        if (isset($payload['email']) && is_string($payload['email'])) {

            $payload['email'] = $this->normalizer->normalizeEmail(
                $this->sanitizer->sanitizeEmail($payload['email'])
            );
        }
        
        try {
            //Vérifier si Email non vide / Valide / N'existe pas déjà dans la base de donnée (donc il a déjà jouer)
            $this->gameContestSubmissionValidator->validateEmail($payload);

            //Vérifie si les RGPD sont acceptés
            $this->gameContestSubmissionValidator->validateRGPD($payload);

            $this->gameContestSubmissionService->handle($payload);

            return new JsonResponse([
                'success' => true,
            ]);
        }  catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/api/game-contest/hasWon', name: 'api_game_contest_hasWon', methods: ['POST'])]
    public function hasWon(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        try {
            $this->gameContestSubmissionService->handleHasWon($payload);

            return new JsonResponse([
                'success' => true,
            ]);
        }  catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


}