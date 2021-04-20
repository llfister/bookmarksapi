<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\BookmarkService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

class BookmarkServiceTest extends TestCase
{
    public function testNotValidUrl(): void
    {
        $bookmarkService = new BookmarkService(new MockHttpClient());
        $urlNotExists = $bookmarkService->checkValidityURL('http://urlfictive.fr');
        $this->assertFalse($urlNotExists);
        $urlEmpty = $bookmarkService->checkValidityURL('');
        $this->assertFalse($urlEmpty);
    }

    public function testValidUrl(): void
    {
        $bookmarkService = new BookmarkService(new MockHttpClient());
        $urlValid = $bookmarkService->checkValidityURL('https://www.google.fr/');
        $this->assertTrue($urlValid);
    }
}
