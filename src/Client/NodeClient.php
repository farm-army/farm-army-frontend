<?php

namespace App\Client;

use App\Entity\CrossFarm;
use App\Repository\CrossFarmRepository;
use App\Repository\CrossPlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\ChainUtil;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class NodeClient
{
    private ClientInterface $client;
    private CacheItemPoolInterface $cacheItemPool;
    private IconResolver $iconResolver;
    private LoggerInterface $logger;
    private string $crossBaseUrl;
    private CrossPlatformRepository $crossPlatformRepository;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        ClientInterface $client,
        CrossPlatformRepository $crossPlatformRepository,
        CacheItemPoolInterface $cacheItemPool,
        IconResolver $iconResolver,
        string $crossBaseUrl,
        LoggerInterface $logger,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
        $this->iconResolver = $iconResolver;
        $this->logger = $logger;
        $this->crossBaseUrl = $crossBaseUrl;
        $this->crossPlatformRepository = $crossPlatformRepository;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    public function getAutofarm(string $chain, string $masterChef, string $address = null): ?array
    {
        $cache = $this->cacheItemPool->getItem('farms-'. md5($chain . json_encode([strtolower($masterChef), strtolower($address ?? '')])));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $parameters = [
            'masterchef' => $masterChef,
            'address' => $address,
        ];

        $uri = $this->crossBaseUrl. '/' . $chain . '/autofarm?' . http_build_query(array_filter($parameters));

        $result = null;

        try {
            $result = json_decode($this->client->request('GET', $uri, [
                'timeout' => 25,
            ])->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('fetch farm error: ' . $e->getMessage());
        }

        $cache->expiresAfter(60 * 5)->set($result);
        $this->cacheItemPool->save($cache);

        return $result;
    }

    public function getPrices(string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('price-' . $chain);

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $prices = json_decode($this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/prices')->getBody()->getContents(), true);

            $cache->expiresAfter(60 * 3)->set($prices);
            $this->cacheItemPool->save($cache);

            return $prices;
        } catch (GuzzleException $e) {
        }

        return [];
    }

    public function getTokenInfo(string $chain, string $address): array
    {
        $cache = $this->cacheItemPool->getItem('token-info-v1-' . md5($address . $chain));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $url = $this->crossBaseUrl . '/' . $chain . '/token/' . urlencode($address);

        try {
            $prices = json_decode($this->client->request('GET', $url)->getBody()->getContents(), true);

            $cache->expiresAfter(60 * 5)->set($prices);
            $this->cacheItemPool->save($cache);

            return $prices;
        } catch (GuzzleException $e) {
        }

        return [];
    }

    public function getTokens(string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('tokens-' . $chain);

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = json_decode($this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/tokens')->getBody()->getContents(), true);

            $cache->expiresAfter(60 * 10)->set($content);
            $this->cacheItemPool->save($cache);

            return $content;
        } catch (GuzzleException $e) {
        }

        return [];
    }

    public function getLiquidityTokens(string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('liquidity-tokens-' . $chain);

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = json_decode($this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/liquidity-tokens')->getBody()->getContents(), true);

            $cache->expiresAfter(60 * 10)->set($content);
            $this->cacheItemPool->save($cache);

            return $content;
        } catch (GuzzleException $e) {
        }

        return [];
    }

    public function getBalances(string $chain, string $address): array
    {
        $cache = $this->cacheItemPool->getItem('balances-' . $chain . '-' . $address);

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $balances = json_decode($this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/balances/' . $address)->getBody()->getContents(), true);

            $cache->expiresAfter(60 * 5)->set($balances);
            $this->cacheItemPool->save($cache);

            return $balances;
        } catch (GuzzleException $e) {
        }

        return [];
    }

    public function getAddressFarms(string $chain, string $address): array
    {
        $cache = $this->cacheItemPool->getItem('farms-v4-' . $address . '-' . $chain);
        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = $this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/all/yield/' . $address, [
                'timeout' => 40,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $body = json_decode($content->getBody()->getContents(), true);

        $balances = [];
        foreach (($body['balances'] ?? []) as $balance) {
            $balances[$balance['token']] = $balance;
        }

        $content = $body['platforms'];

        $priceMap = [];

        $result = [];
        foreach ($content as $platform => $farms) {
            $result[$platform] = $this->crossPlatformRepository->getPlatform($platform);

            $chain = $result[$platform]['chain'];
            if (!array_key_exists($chain, $priceMap)) {
                $priceMap[$chain] = $this->getPrices($chain);
            }

            $prices = $priceMap[$chain];

            if (isset($prices[$result[$platform]['token']])) {
                $result[$platform]['token_price'] = $prices[$result[$platform]['token']];
            }

            $result[$platform]['name'] = $platform;
            $result[$platform]['farms'] = $this->formatFarms($farms);

            $result[$platform]['rewards'] = $this->getTotalRewards($result[$platform]['farms']);

            $result[$platform]['rewards_total'] = array_sum(array_map(static function (array $obj) {
                return $obj['usd'] ?? 0;
            }, $result[$platform]['rewards'] ?? []));

            $result[$platform]['usd'] = $this->getUsd($farms);

            if (isset($result[$platform]['token'])) {
                $token = $result[$platform]['token'];

                if (isset($balances[$token])) {
                    $result[$platform]['wallet'] = $balances[$token];
                }
            }
        }

        uasort($result, function ($a, $b) {
            return ($b['usd'] ?? 0) + ($b['rewards_total'] ?? 0) <=> ($a['usd'] ?? 0) + ($a['rewards_total'] ?? 0);
        });

        $farms = array_values($result);

        $usdTotal = 0.0;
        foreach($farms as $platform1) {
            $usdTotal += ($platform1['usd'] ?? 0.0);
        }

        $usdPending = 0.0;
        foreach ($farms as $platform1) {
            foreach ($platform1['farms'] as $farm) {
                foreach ($farm['rewards'] ?? [] as $reward) {
                    $usdPending += ($reward['usd'] ?? 0.0);
                }
            }
        }

        $wallet = $body['wallet'] ?? [];
        $liquidityPools = $body['liquidityPools'] ?? [];

        $summary = array_filter([
            'wallet' => array_sum(array_map(fn($i) => $i['usd'] ?? 0.0, $wallet)),
            'liquidityPools' => array_sum(array_map(fn($i) => $i['usd'] ?? 0.0, $liquidityPools)),
            'rewards' => $usdPending,
            'vaults' => $usdTotal,
        ]);

        $summary['total'] = array_sum(array_values($summary));
        asort($summary);

        $tokens = [...$wallet, ...$liquidityPools];
        usort($tokens, function ($a, $b) {
            return ($b['usd'] ?? 0) <=> ($a['usd'] ?? 0);
        });

        $values = [
            'wallet' => $tokens,
            'farms' => array_values($result),
            'summary' => $summary,
        ];

        $cache->set($values)->expiresAfter(10);

        $this->cacheItemPool->save($cache);

        return $values;
    }

    public function getAddressFarmsForPlatforms(string $chain, string $address, array $platforms): array
    {
        $cache = $this->cacheItemPool->getItem('farms-v2-platforms-' . $address . '-'   . $chain . '-' . json_encode($platforms));
        if ($cache->isHit()) {
            return $cache->get();
        }

        $uri = $this->crossBaseUrl . '/' . $chain . '/yield/' . urlencode($address) . '?' . http_build_query(['p' => implode(',', $platforms)]);

        try {
            $content = $this->client->request('GET', $uri, [
                'timeout' => 40,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $content = json_decode($content->getBody()->getContents(), true);

        $result = [];

        $priceMap = [];

        foreach ($content as $platform => $farms) {
            $result[$platform] = $this->crossPlatformRepository->getPlatform($platform);


            $chain = $result[$platform]['chain'];
            if (!array_key_exists($chain, $priceMap)) {
                $priceMap[$chain] = $this->getPrices($chain);
            }

            $prices = $priceMap[$chain];

            if (isset($result[$platform]['token'], $prices[$result[$platform]['token']])) {
                $result[$platform]['token_price'] = $prices[$result[$platform]['token']];
            }

            $result[$platform]['name'] = $platform;
            $result[$platform]['farms'] = $this->formatFarms($farms);

            $result[$platform]['rewards'] = $this->getTotalRewards($result[$platform]['farms']);

            $result[$platform]['rewards_total'] = array_sum(array_map(static function (array $obj) {
                return $obj['usd'] ?? 0;
            }, $result[$platform]['rewards'] ?? []));

            $result[$platform]['usd'] = $this->getUsd($farms);

            if (isset($result[$platform]['token'])) {
                $token = $result[$platform]['token'];

                if (isset($balances[$token])) {
                    $result[$platform]['wallet'] = $balances[$token];
                }
            }
        }

        $cache->set($result)->expiresAfter(10);

        $this->cacheItemPool->save($cache);

        return $result;
    }

    public function getWallet(string $chain, string $address): array
    {
        $cache = $this->cacheItemPool->getItem('wallet-v1-' . md5($address . $chain));
        if ($cache->isHit()) {
            return $cache->get();
        }

        $uri = $this->crossBaseUrl . '/' . $chain . '/wallet/' . urlencode($address);

        try {
            $content = $this->client->request('GET', $uri, [
                'timeout' => 40,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $result = json_decode($content->getBody()->getContents(), true);

        $cache->set($result)->expiresAfter(60 * 5);

        $this->cacheItemPool->save($cache);

        return $result;
    }

    /**
     * @param $farms1
     * @return array
     */
    private function getTotalRewards(array $farms1): array
    {
        $allRewards = [];

        foreach ($farms1 as $farm) {
            foreach ($farm['rewards'] ?? [] as $reward) {
                if (!isset($allRewards[$reward['symbol']])) {
                    $allRewards[$reward['symbol']] = array_merge($reward, [
                        'amount' => 0,
                        'usd' => 0,
                    ]);
                }

                $allRewards[$reward['symbol']]['amount'] += $reward['amount'] ?? 0;
                $allRewards[$reward['symbol']]['usd'] += $reward['usd'] ?? 0;
            }
        }

        $allRewards = array_values($allRewards);

        uasort($allRewards, function($a, $b) {
            return $b['usd'] <=> $a['usd'];
        });

        return array_slice($allRewards, 0, 5);
    }

    private function getUsd(array $farms): float
    {
        $usd = 0.0;

        foreach ($farms as $farm) {
            $usd += ($farm['deposit']['usd'] ?? 0.0);
        }

        foreach ($farms as $farm) {
            foreach ($farm['rewards'] ?? [] as $reward) {
                $usd += ($reward['usd'] ?? 0.0);
            }
        }

        return $usd;
    }

    public function formatFarms(array $farms): array
    {
        foreach ($farms as $key => $farm) {
            $token = $farm['farm']['name'];

            if (isset($farm['farm']['token'])) {
                $token = $farm['farm']['token'];
            }

            $farms[$key]['icon'] = $this->iconResolver->getIcon($token, $farm['farm']['chain']);

            if (isset($farm['rewards'])) {
                $farms[$key]['rewards'] = array_map(function (array $reward) use ($farm) {
                    $reward['icon'] = $this->iconResolver->getIcon($reward['symbol'] ?? 'unknown', $farm['farm']['chain']);
                    return $reward;
                }, $farm['rewards']);
            }

            $farms[$key]['farm_rewards'] = array_sum(array_map(static function (array $obj) {
                return $obj['usd'] ?? 0;
            }, $farm['rewards'] ?? []));
        }

        uasort($farms, function($a, $b) {
            $bV = ($b['deposit']['usd'] ?? 0) + ($b['farm_rewards'] ?? 0);
            $aV = ($a['deposit']['usd'] ?? 0) + ($a['farm_rewards'] ?? 0);

            return $bV <=> $aV;
        });

        return array_values($farms);
    }

    public function getDetails(string $chain, string $address, string $farmId): array
    {
        $cache = $this->cacheItemPool->getItem('farms-v1-details-' . md5($address . $farmId . $chain));

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = $this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/details/' . $address . '/' . $farmId, [
                'timeout' => 40,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $result = json_decode($content->getBody()->getContents(), true);

        $this->cacheItemPool->save($cache->set($result)->expiresAfter(60 * 5));

        return $result;
    }

    public function getTransactions(string $address, string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('address-transactions-v1-' . $chain .  '-' . md5($address));

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = $this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/transactions/' . $address, [
                'timeout' => 25,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $transactions = json_decode($content->getBody()->getContents(), true);

        $groupedHash = [];
        foreach ($transactions as $key => $transaction) {
            if (isset($transaction['vault']['provider'])) {
                $transactions[$key]['provider'] = $this->crossPlatformRepository->getPlatform($transaction['vault']['provider']);
            }

            if (isset($transaction['symbol'])) {
                $transactions[$key]['icon'] = $this->iconResolver->getIcon($transaction['symbol'], $chain);
            }

            if (!isset($groupedHash[$transaction['hash']])) {
                $groupedHash[$transaction['hash']] = [];
            }

            $groupedHash[$transaction['hash']][] = $transactions[$key];
        }

        $groupedHash = array_values($groupedHash);

        $this->cacheItemPool->save($cache->set($groupedHash)->expiresAfter(60));

        return $groupedHash;
    }

    public function getNfts(string $address, string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('address-nfts-v1-' . $chain .  '-' . md5($address));

        if ($cache->isHit()) {
            return $cache->get();
        }

        try {
            $content = $this->client->request('GET', $this->crossBaseUrl . '/' . $chain . '/nft/' . $address, [
                'timeout' => 25,
            ]);
        } catch (GuzzleException $e) {
            return [];
        }

        $transactions = json_decode($content->getBody()->getContents(), true);

        $collections = $transactions['collections'] ?? [];

        usort($collections, function ($a, $b) {
            return ($b['balance'] ?? 0) <=> ($a['balance'] ?? 0);
        });

        $this->cacheItemPool->save($cache->set($collections)->expiresAfter(60 * 15));

        return $collections;
    }

    public function updateFarms(bool $force = false): void
    {
        $cache = $this->cacheItemPool->getItem('crossfarms-v1');

        if (!$force && $cache->isHit()) {
            return;
        }

        foreach (ChainUtil::getChains() as $chain) {
            $uri = sprintf("%s/%s/farms", $this->crossBaseUrl, $chain['id']);

            $this->logger->info('fetching: ' . $uri);

            try {
                $content = json_decode($this->client->request('GET', $uri, [
                    'timeout' => 60,
                ])->getBody()->getContents(), true);
            } catch (GuzzleException $e) {
                $this->logger->error('fetch farm error: ' . $e->getMessage());
                continue;
            }

            $this->crossFarmRepository->update($content);
        }

        $cache->expiresAfter(60 * 60)->set(['success' => true]);
        $this->cacheItemPool->save($cache);
    }
}