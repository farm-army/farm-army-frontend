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

    public function renderAllFarms(string $template = 'components/farms_mini.html.twig'): array
    {
        $this->triggerFetchUpdate();

        return $this->renderFarms(
            array_map(static fn(array $f) => $f['json'], $this->farmRepository->getAllValid()),
            $template
        );
    }

    public function renderFarms(array $farms, string $template = 'components/farms_mini.html.twig'): array
    {
        return array_map(function (array $farm) use ($template): array {
            $farm = $this->enrichFarmData($farm);

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
        }, $farms);
    }

    public function triggerFetchUpdate(): void
    {
        $this->nodeClient->getFarms();
    }

    public function generateApiFarms(): array
    {
        $cache = $this->cacheItemPool->getItem('generate-api-farms-v2');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $this->triggerFetchUpdate();

        $myFarms = [];
        foreach ($this->farmRepository->getAllValid() as $farm) {
            $myFarms[] = $this->enrichFarmData($farm['json']);
        }

        $cache->expiresAfter(60 * 5)->set($myFarms);
        $this->cacheItemPool->save($cache);

        return $myFarms;
    }

    public function enrichFarmData(array $farm): array
    {
        $token = $farm['token'] ?? $farm['name'];

        $farm['icon'] = '?';
        if ($token) {
            $farm['icon'] = $this->iconResolver->getIcon($token);
        }

        $farm['provider'] = $this->platformRepository->getPlatform($farm['provider']);

        if (isset($farm['yield']['apr']) && $farm['yield']['apr'] > 0) {
            $farm['yield']['daily'] = $farm['yield']['apr'] / 365;
        } elseif (isset($farm['yield']['apy']) && $farm['yield']['apy'] > 0) {
            $farm['yield']['daily'] = InterestUtil::apyToApr($farm['yield']['apy'] / 100);
        }

        return $farm;
    }
}