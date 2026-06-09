<?php

namespace App\Admin\Log\DTO;

use Symfony\Component\HttpFoundation\Request;

final readonly class LogFilter
{
    public function __construct(
        public string $fileKey,
        public ?string $level,
        public ?string $search,
        public ?string $preset,
        public int $page,
        public int $limit,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            fileKey: $request->query->get('file', 'current'),
            level: $request->query->get('level'),
            search: $request->query->get('search'),
            preset: $request->query->get('preset'),
            page: max(1, $request->query->getInt('page', 1)),
            limit: 100,
        );
    }
}