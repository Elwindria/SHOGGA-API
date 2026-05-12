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

        try {
            $this->gameContestSubmissionValidator->validateEmail($payload);

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