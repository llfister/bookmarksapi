<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Entity\Bookmark;
use App\Service\OEmbedInterface;
use Embera\Embera;

final class BookmarkFormatter implements OEmbedInterface
{
    /**
     * @return array<int, string>
     */
    public function getUrlData(string $url): array
    {
        $embed = new Embera();

        return $embed->getUrlData($url);
    }

    public function getOEmbedData(string $url, Bookmark $bookmark): Bookmark
    {
        $infos = $this->getUrlData($url);

        return $this->format($infos, $bookmark);
    }

    /**
     * @param array|mixed[] $infos
     */
    public function format(array $infos, Bookmark $bookmark): Bookmark
    {
        if (null !== array_key_first($infos)) {
            $info = $infos[array_key_first($infos)];

            if ('' === $bookmark->getTitle()) {
                $bookmark->setTitle($info['title']);
            }

            if ('video' === $info['type']) {
                $bookmark->setDuration($info['duration']);
            }

            $bookmark->setAuthor($info['author_name']);
            $bookmark->setWidth($info['width']);
            $bookmark->setHeight($info['height']);
        }

        return $bookmark;
    }
}
