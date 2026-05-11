<?php

namespace App\Controller\Api;

use App\Sellsy\Company\SellsyCompanyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GameContestController extends AbstractController
{
    public function __construct(
        private SellsyCompanyService $sellsyCompanyService,
    ) {
    }

    #[Route('/api/game-contest/submit', name: 'api_game_contest_submit', methods: ['POST'])]
    public function submit(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json([
                'success' => false,
                'message' => 'Payload JSON invalide.',
            ], 400);
        }

        $email = $payload['email'] ?? null;

        if ($this->sellsyCompanyService->companyExistsByEmail($email)) {
            throw new \RuntimeException('Email déjà présent dans Sellsy.');
        }

        return $this->json([
            'success' => true,
            'message' => 'Formulaire reçu.',
            'data' => $payload,
        ]);
    }
}