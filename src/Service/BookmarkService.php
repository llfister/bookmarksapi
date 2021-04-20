<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BookmarkService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function checkValidityURL(string $url): bool
    {
        if (!@get_headers($url)) {
            return false;
        }
        if ('' === $url) {
            return false;
        }

        $result = $this->client->request(
            'GET',
            $url
        );
        $statusCode = $result->getStatusCode();
        if (200 === $statusCode) {
            return true;
        }

        return false;
    }
}
