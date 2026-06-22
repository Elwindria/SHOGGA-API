<?php

namespace App\Admin\Dashboard\DTO;

final readonly class RateLimiterStats
{
    public function __construct(
        public int $blockedCount,
        public ?string $lastBlockedIp,
        public ?string $lastBlockedPath,
        public ?string $lastBlockedDate,
    ) {
    }
}