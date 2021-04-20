<?php

declare(strict_types=1);

namespace App\Service;

interface OEmbedInterface
{
    /**
     * @return array<int, string>
     */
    public function getUrlData(string $urls): array;
}
