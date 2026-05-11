<?php

namespace App\Shared\Normalizer;

final class Normalizer
{
    public function normalizeEmail(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    public function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }
}