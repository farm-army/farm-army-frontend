<?php

namespace App\Pools;

use App\Repository\CrossFarmRepository;
use App\Repository\CrossPlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\InterestUtil;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Twig\Environment;

class FarmPools
{
    private $iconResolver;
    private $cacheItemPool;
    private Environment $environment;
    private CrossPlatformRepository $crossPlatformRepository;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        IconResolver $iconResolver,
        CacheItemPoolInterface $cacheItemPool,
        Environment $environment,
        CrossPlatformRepository $crossPlatformRepository,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->iconResolver = $iconResolver;
        $this->cacheItemPool = $cacheItemPool;
        $this->environment = $environment;
        $this->crossPlatformRepository = $crossPlatformRepository;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    public function renderAllFarms(string $chain, string $template = 'components/farms_mini.html.twig'): array
    {
        return $this->renderFarms(
            array_map(static fn(array $f) => $f['json'], $this->crossFarmRepository->getAllValid($chain)),
            $template
        );
    }

    public function renderFarms(array $farms, string $template = 'components/farms_mini.html.twig', array $arguments = []): array
    {
        return array_map(function (array $farm) use ($template, $arguments): array {
            $farm = $this->enrichFarmData($farm);

            $arr = [
                'id' => $farm['id'],
                'name' => $farm['name'],
                'platform' => $farm['provider']['id'],
                'content' => $this->environment->render($template, array_merge($arguments, [
                    'farm' => $farm,
                ]))
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

    public function generateApiFarms(string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('generate-api-farms-v2-' . $chain);

        if ($cache->isHit()) {
            return $cache->get();
        }

        $myFarms = [];
        foreach ($this->crossFarmRepository->getAllValid($chain) as $farm) {
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
            $farm['icon'] = $this->iconResolver->getIcon($token, $farm['chain']);
        }

        $farm['provider'] = $this->crossPlatformRepository->getPlatformOnChain($farm['chain'], $farm['provider']);

        if (isset($farm['yield']['apr']) && $farm['yield']['apr'] > 0) {
            $farm['yield']['daily'] = $farm['yield']['apr'] / 365;
        } elseif (isset($farm['yield']['apy']) && $farm['yield']['apy'] > 0) {
            $farm['yield']['daily'] = InterestUtil::apyToApr($farm['yield']['apy'] / 100);
        }

        if (isset($farm['earns']) && empty($farm['earn'])) {
            $farm['earn'] = array_map(function (string $symbol) use ($farm) {
                return [
                    'symbol' => $symbol,
                    'icon' => $this->iconResolver->getTokenIconForSymbolAddress($farm['chain'], [['symbol' => $symbol]]),
                ];
            }, $farm['earns']);
        }

        if (isset($farm['earn'])) {
            $farm['earn'] = array_map(function (array $earn) use ($farm) {
                if (!isset($earn['icon'])) {
                    $earn['icon'] = $this->iconResolver->getTokenIconForSymbolAddress($farm['chain'], [$earn]);
                }

                return $earn;
            }, $farm['earn']);
        }

        return $farm;
    }
}