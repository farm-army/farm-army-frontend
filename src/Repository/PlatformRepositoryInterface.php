<?php

namespace App\Repository;

interface PlatformRepositoryInterface
{
    public function getPlatform(string $id): array;

    public function getPlatformChunks(): array;

    public function getPlatforms(): array;
}