<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class BookmarkControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $process = new Process(['php', 'bin/console', 'doctrine:fixtures:load', '--env=test']);
        $process->run();

        parent::setUp();
    }

    public function postRoute($url): void
    {
        $this->client = static::createClient();
        $this->client->request(
            'POST',
            '/bookmarks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{
                '.$url.'
                "title" : "An awesome video !",
                "keywords" : [
                    {
                        "name" : "shark"
                    },
                    {
                        "name" : "swim"
                    }
                ]
            }'
        );
    }

    public function testCreateSuccess(): void
    {
        $this->postRoute('"url" : "https://vimeo.com/470201160",');

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateNotVimeoOrFlickrUrl(): void
    {
        $this->postRoute('"url" : "https://www.google.fr/",');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testList(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/bookmarks'
        );

        $this->assertResponseIsSuccessful();
    }
}
