<?php

namespace App\GameContest\Validator;

use App\Sellsy\Company\SellsyCompanyService;
use App\Sellsy\Individual\SellsyIndividualService;

final class GameContestSubmissionValidator
{
    public function __construct(
        private SellsyCompanyService $sellsyCompanyService,
        private SellsyIndividualService $sellsyIndividualService,
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

        if ($this->sellsyIndividualService->individualExistsByEmail($email)) {
            throw new \RuntimeException('Cet email existe déjà dans Sellsy.');
        }
    }

    public function validateRGPD(array $payload): void
    {
        if (!filter_var($payload['rgpd'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            throw new \InvalidArgumentException('RGPD non acceptés');
        }
    }
}