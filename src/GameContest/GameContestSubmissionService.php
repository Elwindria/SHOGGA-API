<?php

namespace App\GameContest;

final class GameContestSubmissionService
{
    public function __construct(
    ) {
    }

    public function handle(array $payload): void
    {
        if ($payload["hasWon"]) {
            if ($payload["rewardType"] === "-10%" || $payload["rewardType"] === "-20%") {
                //envoyé le mail via brevo ?
            }    
        }

        if ($payload["newsletter"]) {
            // plus tard : création Sellsy si cases cochées
        }
    }
}