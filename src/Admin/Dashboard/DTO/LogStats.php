<?php

namespace App\Admin\Dashboard\DTO;

final readonly class LogStats
{
    public function __construct(
        public int $total,
        public int $errors,
        public int $warnings,
        public int $infos,
    ) {
    }
}