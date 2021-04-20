<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookmarkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookmarkRepository::class)
 */
class Bookmark implements BookmarkInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @Groups({"get_bookmark"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"get_bookmark"})
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern="#^((https?):\/\/)?([a-z0-9\-\.]*)(vimeo\.com|flickr\.com)(\/([a-z0-9+%-]\.?)+)*\/?#",
     *     match=true,
     *     message="The URL must come from Vimeo or Flickr"
     * )
     * @Groups({"get_bookmark"})
     */
    private string $url;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get_bookmark"})
     */
    private \DateTime $createdDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Groups({"get_bookmark"})
     */
    private int $height;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Groups({"get_bookmark"})
     */
    private int $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"get_bookmark"})
     */
    private ?int $duration;

    /**
     * @ORM\ManyToMany(targetEntity=Keyword::class, inversedBy="bookmarks", cascade={"persist"})
     * @Groups({"get_bookmark"})
     *
     * @var ArrayCollection<int, Keyword>
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_bookmark"})
     */
    private string $author;

    public function __construct()
    {
        $this->keywords = new ArrayCollection();
        $this->createdDate = new \DateTime();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedDate(): \DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = new \DateTime();

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return ArrayCollection<int, Keyword>
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    public function addKeyword(Keyword $keyword): self
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords[] = $keyword;
            $keyword->addBookmark($this);
        }

        return $this;
    }

    public function removeKeyword(Keyword $keyword): self
    {
        if ($this->keywords->removeElement($keyword)) {
            $keyword->removeBookmark($this);
        }

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }
}
