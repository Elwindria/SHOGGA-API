<?php

namespace App\GameContest\Validator;

use App\Sellsy\Company\SellsyCompanyService;

final class GameContestSubmissionValidator
{
    public function __construct(
        private SellsyCompanyService $sellsyCompanyService,
    ) {
    }

    public function validateEmail(array $payload): void
    {
        $email = $payload['email'] ?? null;

        if ($email === null || trim($email) === '') {
            throw new \InvalidArgumentException('Email manquant.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide.');
        }

        if ($this->sellsyCompanyService->companyExistsByEmail($email)) {
            throw new \RuntimeException('Cet email existe déjà dans Sellsy.');
        }
    }

    public function validateRGPD(array $payload): void
    {
        if ($payload["rgpd"]) {
            throw new \InvalidArgumentException('RGPD non acceptés');
        }
    }
}