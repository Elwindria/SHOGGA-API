<?php

namespace App\Shared\Sanitizer;

final class Sanitizer
{
    public function sanitizeEmail(string $email): string
    {
        $email = trim($email);

        // évite injection headers email
        $email = str_replace(["\r", "\n"], '', $email);

        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}