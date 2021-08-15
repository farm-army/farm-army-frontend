<?php declare(strict_types=1);

namespace App\Repository;

use App\Utils\ChainGuesser;

class PlatformRepository implements PlatformRepositoryInterface
{
    private PlatformBscRepository $bscRepository;
    private PlatformPolygonRepository $platformPolygonRepository;
    private PlatformFantomRepository $platformFantomRepository;
    private PlatformKccRepository $platformKccRepository;
    private ChainGuesser $chainGuesser;

    public function __construct(
        PlatformBscRepository $bscRepository,
        PlatformPolygonRepository $platformPolygonRepository,
        PlatformFantomRepository $platformFantomRepository,
        PlatformKccRepository $platformKccRepository,
        ChainGuesser $chainGuesser
    ) {
        $this->bscRepository = $bscRepository;
        $this->platformPolygonRepository = $platformPolygonRepository;
        $this->platformFantomRepository = $platformFantomRepository;
        $this->platformKccRepository = $platformKccRepository;
        $this->chainGuesser = $chainGuesser;
    }

    public function getPlatform(string $id): array
    {
        switch ($this->chainGuesser->getChain()) {
            case 'bsc':
                return $this->bscRepository->getPlatform($id);
            case 'polygon':
                return $this->platformPolygonRepository->getPlatform($id);
            case 'fantom':
                return $this->platformFantomRepository->getPlatform($id);
            case 'kcc':
                return $this->platformKccRepository->getPlatform($id);
            default:
                throw new \InvalidArgumentException('Invalid platform');
        }
    }

    public function getPlatformChunks(): array
    {
        switch ($this->chainGuesser->getChain()) {
            case 'bsc':
                return $this->bscRepository->getPlatformChunks();
            case 'polygon':
                return $this->platformPolygonRepository->getPlatformChunks();
            case 'fantom':
                return $this->platformFantomRepository->getPlatformChunks();
            case 'kcc':
                return $this->platformKccRepository->getPlatformChunks();
            default:
                throw new \InvalidArgumentException('Invalid platform');
        }
    }

    public function getPlatforms(): array
    {
        switch ($this->chainGuesser->getChain()) {
            case 'bsc':
                return $this->bscRepository->getPlatforms();
            case 'polygon':
                return $this->platformPolygonRepository->getPlatforms();
            case 'fantom':
                return $this->platformFantomRepository->getPlatforms();
            case 'kcc':
                return $this->platformKccRepository->getPlatforms();
            default:
                throw new \InvalidArgumentException('Invalid platform');
        }
    }
}