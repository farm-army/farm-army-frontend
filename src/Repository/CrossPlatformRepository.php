<?php declare(strict_types=1);

namespace App\Repository;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class CrossPlatformRepository
{
    private PlatformBscRepository $bscRepository;
    private PlatformPolygonRepository $platformPolygonRepository;
    private PlatformFantomRepository $platformFantomRepository;
    private PlatformKccRepository $platformKccRepository;
    private PlatformHarmonyRepository $platformHarmonyRepository;
    private PlatformCeloRepository $platformCeloRepository;
    private PlatformMoonriverRepository $platformMoonriverRepository;
    private PlatformCronosRepository $platformCronosRepository;
    private CacheItemPoolInterface $cacheItemPool;
    private PlatformMoonbeamRepository $platformMoonbeamRepository;
    private LoggerInterface $logger;

    public function __construct(
        PlatformBscRepository $bscRepository,
        PlatformPolygonRepository $platformPolygonRepository,
        PlatformFantomRepository $platformFantomRepository,
        PlatformKccRepository $platformKccRepository,
        PlatformHarmonyRepository $platformHarmonyRepository,
        PlatformCeloRepository $platformCeloRepository,
        PlatformMoonriverRepository $platformMoonriverRepository,
        PlatformCronosRepository $platformCronosRepository,
        PlatformMoonbeamRepository $platformMoonbeamRepository,
        CacheItemPoolInterface $cacheItemPool,
        LoggerInterface $logger
    ) {
        $this->bscRepository = $bscRepository;
        $this->platformPolygonRepository = $platformPolygonRepository;
        $this->platformFantomRepository = $platformFantomRepository;
        $this->platformKccRepository = $platformKccRepository;
        $this->platformHarmonyRepository = $platformHarmonyRepository;
        $this->platformCeloRepository = $platformCeloRepository;
        $this->platformMoonriverRepository = $platformMoonriverRepository;
        $this->platformCronosRepository = $platformCronosRepository;
        $this->cacheItemPool = $cacheItemPool;
        $this->platformMoonbeamRepository = $platformMoonbeamRepository;
        $this->logger = $logger;
    }

    public function getPlatformsOnChain(string $chain): array
    {
        switch ($chain) {
            case 'bsc':
                return $this->bscRepository->getPlatforms();
            case 'polygon':
                return $this->platformPolygonRepository->getPlatforms();
            case 'fantom':
                return $this->platformFantomRepository->getPlatforms();
            case 'kcc':
                return $this->platformKccRepository->getPlatforms();
            case 'harmony':
                return $this->platformHarmonyRepository->getPlatforms();
            case 'celo':
                return $this->platformCeloRepository->getPlatforms();
            case 'moonriver':
                return $this->platformMoonriverRepository->getPlatforms();
            case 'cronos':
                return $this->platformCronosRepository->getPlatforms();
            case 'moonbeam':
                return $this->platformMoonbeamRepository->getPlatforms();
            default:
                throw new \InvalidArgumentException('Invalid platform');
        }
    }

    public function getPlatformChunksOnChain(string $chain): array
    {
        switch ($chain) {
            case 'bsc':
                return $this->bscRepository->getPlatformChunks();
            case 'polygon':
                return $this->platformPolygonRepository->getPlatformChunks();
            case 'fantom':
                return $this->platformFantomRepository->getPlatformChunks();
            case 'kcc':
                return $this->platformKccRepository->getPlatformChunks();
            case 'harmony':
                return $this->platformHarmonyRepository->getPlatformChunks();
            case 'celo':
                return $this->platformCeloRepository->getPlatformChunks();
            case 'moonriver':
                return $this->platformMoonriverRepository->getPlatformChunks();
            case 'cronos':
                return $this->platformCronosRepository->getPlatformChunks();
            case 'moonbeam':
                return $this->platformMoonbeamRepository->getPlatformChunks();
            default:
                throw new \InvalidArgumentException('invalid chain');
        }
    }

    public function getPlatformOnChain(string $chain, string $id): array
    {
        switch ($chain) {
            case 'bsc':
                return $this->bscRepository->getPlatform($id);
            case 'polygon':
                return $this->platformPolygonRepository->getPlatform($id);
            case 'fantom':
                return $this->platformFantomRepository->getPlatform($id);
            case 'kcc':
                return $this->platformKccRepository->getPlatform($id);
            case 'harmony':
                return $this->platformHarmonyRepository->getPlatform($id);
            case 'celo':
                return $this->platformCeloRepository->getPlatform($id);
            case 'moonriver':
                return $this->platformMoonriverRepository->getPlatform($id);
            case 'cronos':
                return $this->platformCronosRepository->getPlatform($id);
            case 'moonbeam':
                return $this->platformMoonbeamRepository->getPlatform($id);
            default:
                $this->logger->error(sprintf("Invalid platform '%s' '%s'", $chain, $id));

                return [
                    'id' => $id,
                    'label' => ucfirst($id),
                    'chain' => $chain,
                    'icon' => '/assets/platforms/unknown.png',
                ];
        }
    }

    private function getPlatformMap(): array
    {
        $cacheItem = $this->cacheItemPool->getItem('cross-platforms-map');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $allPlatforms = [
            ...$this->bscRepository->getPlatforms(),
            ...$this->platformPolygonRepository->getPlatforms(),
            ...$this->platformFantomRepository->getPlatforms(),
            ...$this->platformKccRepository->getPlatforms(),
            ...$this->platformHarmonyRepository->getPlatforms(),
            ...$this->platformCeloRepository->getPlatforms(),
            ...$this->platformMoonriverRepository->getPlatforms(),
            ...$this->platformCronosRepository->getPlatforms(),
            ...$this->platformMoonbeamRepository->getPlatforms()
        ];

        $result = [];

        foreach ($allPlatforms as $platform) {
            $result[$platform['id']] = $platform;
        }

        $this->cacheItemPool->save($cacheItem->set($result)->expiresAfter(60 * 60 * 3));

        return $result;
    }

    public function getPlatform(string $id): array
    {
        $platforms = $this->getPlatformMap();

        if (isset($platforms[$id])) {
            return $platforms[$id];
        }

        return [
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
        ];
    }

    public function getPlatforms(): array
    {
        return array_values($this->getPlatformMap());
    }

    public function getPlatformChunks(): array
    {
        return [
            ...$this->bscRepository->getPlatformChunks(),
            ...$this->platformPolygonRepository->getPlatformChunks(),
            ...$this->platformFantomRepository->getPlatformChunks(),
            ...$this->platformKccRepository->getPlatformChunks(),
            ...$this->platformHarmonyRepository->getPlatformChunks(),
            ...$this->platformCeloRepository->getPlatformChunks(),
            ...$this->platformMoonriverRepository->getPlatformChunks(),
            ...$this->platformCronosRepository->getPlatformChunks(),
            ...$this->platformMoonbeamRepository->getPlatformChunks()
        ];
    }
}