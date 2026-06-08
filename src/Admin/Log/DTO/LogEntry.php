<?php

namespace App\Admin\Log\DTO;

final readonly class LogEntry
{
    public function __construct(
        public string $raw,
        public ?string $date,
        public ?string $level,
        public string $message,
    ) {
    }
}