<?php

namespace App\GameContest\Service;

use App\Sellsy\Individual\SellsyIndividualService;
use Psr\Log\LoggerInterface;

final class GameContestExpiredIndividualsCleaner
{
    public function __construct(
        private readonly SellsyIndividualService $sellsyIndividualService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function cleanExpiredIndividualsFromGameContest(): int
    {
        $count = 0;

        $this->logger->info('[Sellsy][GameContest] Starting SHOGGA Game Contest RGPD cleanup');

        $expiredIndividuals = $this->sellsyIndividualService->findExpiredIndividualsFromGameContest();

        foreach ($expiredIndividuals['data'] ?? [] as $individual) {
            $this->sellsyIndividualService->deleteIndividual($individual['id']);
            $count++;

            $this->logger->info('[Sellsy][GameContest] Deleted expired SHOGGA individual', [
                'individual_id' => $individual['id'],
                'email' => $individual['email'] ?? null,
            ]);
        }

        $this->logger->info('[Sellsy][GameContest] SHOGGA Game Contest RGPD cleanup completed', [
            'deleted_count' => $count,
        ]);

        return $count;
    }
}