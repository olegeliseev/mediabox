<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'files')]
class File
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column]
    private string $hash;

    #[ORM\Column]
    private string $fileName;

    #[ORM\Column]
    private string $extension;

    #[ORM\Column]
    private int $fileSize;

    #[ORM\Column]
    private bool $isImg;

    #[ORM\Column]
    private \DateTime $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getFileSize(): int|float
    {
        return $this->fileSize;
    }

    public function setFileSize(int|float $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getIsImg(): bool
    {
        return $this->isImg;
    }

    public function setIsImg(bool $isImg): void
    {
        $this->isImg = $isImg;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
