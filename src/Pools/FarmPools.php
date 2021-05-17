<?php

namespace App\Pools;

use App\Client\NodeClient;
use App\Repository\FarmRepository;
use App\Repository\PlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\InterestUtil;
use Psr\Cache\CacheItemPoolInterface;
use Twig\Environment;

class FarmPools
{
    private $nodeClient;
    private $iconResolver;
    private $platformRepository;
    private $cacheItemPool;
    private Environment $environment;
    private FarmRepository $farmRepository;

    public function __construct(
        NodeClient $nodeClient,
        IconResolver $iconResolver,
        PlatformRepository $platformRepository,
        CacheItemPoolInterface $cacheItemPool,
        Environment $environment,
        FarmRepository $farmRepository
    )
    {
        $this->nodeClient = $nodeClient;
        $this->iconResolver = $iconResolver;
        $this->platformRepository = $platformRepository;
        $this->cacheItemPool = $cacheItemPool;
        $this->environment = $environment;
        $this->farmRepository = $farmRepository;
    }

    public function generateContent(string $template = 'components/farms_mini.html.twig'): array
    {
        $cache = $this->cacheItemPool->getItem('generate-farms-content-' . md5($template));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $farms = array_map(function(array $farm) use ($template): array {
            $arr = [
                'id' => $farm['id'],
                'name' => $farm['name'],
                'platform' => $farm['provider']['id'],
                'content' => $this->environment->render($template, [
                    'farm' => $farm,
                ])
            ];

            if (isset($farm['tvl']['usd'])) {
                $arr['tvl'] = $farm['tvl']['usd'];
            }

            if (isset($farm['yield']['daily'])) {
                $arr['yield'] = $farm['yield']['daily'];
            }

            if (isset($farm['earns'])) {
                $arr['earns'] = $farm['earns'];
            }

            return $arr;
        }, $this->generateFarms());

        $cache->expiresAfter(60 * 15)->set($farms);
        $this->cacheItemPool->save($cache);

        return $farms;
    }

    public function generateFarm(array $farm, array $hots = [], $new = []): array
    {
        $cache = $this->cacheItemPool->getItem('generate-farm-single-' . md5(json_encode([$farm['id'], $hots, $new])));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $token = $farm['name'];

        if (isset($farm['token'])) {
            $token = $farm['token'];
        }

        $farm['icon'] = '?';
        if ($token) {
            $farm['icon'] = $this->iconResolver->getIcon($token);
        }

        $farm['provider'] = $this->platformRepository->getPlatform($farm['provider']);

        if (isset($farm['yield']['apr']) && $farm['yield']['apr'] > 0) {
            $farm['yield']['daily'] = $farm['yield']['apr'] / 365;
        } else {
            if (isset($farm['yield']['apy']) && $farm['yield']['apy'] > 0) {
                $farm['yield']['daily'] = InterestUtil::apyToApr($farm['yield']['apy'] / 100);
            }
        }

        if (in_array($farm['id'], $hots, true)) {
            $farm['hot'] = true;
        }

        if (in_array($farm['id'], $new, true)) {
            $farm['new'] = true;
        }

        $cache->expiresAfter(60 * 1)->set($farm);
        $this->cacheItemPool->save($cache);

        return $farm;
    }

    public function generateFarms(): array
    {
        $cache = $this->cacheItemPool->getItem('generate-farms');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $farms = $this->nodeClient->getFarms();

        uasort($farms, function ($a, $b) {
            return ($b['tvl']['usd'] ?? 0) <=> ($a['tvl']['usd'] ?? 0);
        });

        $hots = array_map(function ($item) {
            return $item['id'];
        }, array_slice($farms, 0, 5));

        $new = $this->farmRepository->getNewFarm();

        $myFarms = [];

        foreach ($farms as $farm) {
            $myFarms[] = $this->generateFarm($farm, $hots, $new);
        }

        $cache->expiresAfter(60 * 5)->set($myFarms);
        $this->cacheItemPool->save($cache);

        $this->farmRepository->update($farms);

        return $myFarms;
    }
}