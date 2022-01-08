<?php declare(strict_types=1);

namespace App\Repository;

use Psr\Cache\CacheItemPoolInterface;

class NftRepository
{
    private string $projectDir;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(string $projectDir, CacheItemPoolInterface $cacheItemPool)
    {
        $this->projectDir = $projectDir;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function getCollectionInfo(string $address): ?array
    {
        $cacheItem = $this->cacheItemPool->getItem('nft-collection-info-v5-' . md5($address));

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $info = null;

        if (file_exists($this->projectDir . '/public/assets/nfts/' . $address . '.png')) {
            $info = [
                'icon' => 'assets/nfts/' . $address . '.png',
            ];
        }

        $this->cacheItemPool->save($cacheItem->set($info)->expiresAfter(60 * 60));

        return $info;
    }
}
