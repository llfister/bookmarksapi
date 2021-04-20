<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

interface BookmarkInterface
{
    public function getTitle(): string;

    public function getUrl(): string;

    public function getCreatedDate(): \DateTimeInterface;

    public function getHeight(): int;

    public function getWidth(): int;

    /**
     * @return ArrayCollection<int, Keyword>
     */
    public function getKeywords();

    public function getAuthor(): ?string;
}
