<?php

declare(strict_types=1);

namespace App\DataObjects;

class FileData
{
    public function __construct(
        public readonly string $hash,
        public readonly string $fileName,
        public readonly string $extension,
        public readonly int $fileSize,
        public readonly bool $isImg,
        public readonly \DateTime $createdAt
    ) {
    }
}
