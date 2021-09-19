<?php

namespace App\Symbol;

use App\Utils\ChainGuesser;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IconResolver
{
    private string $projectDir;
    private UrlGeneratorInterface $urlGenerator;
    private CacheItemPoolInterface $cacheItemPool;
    private int $assetVersion = 3;
    private ChainGuesser $chainGuesser;

    public function __construct(string $projectDir, UrlGeneratorInterface $urlGenerator, CacheItemPoolInterface $cacheItemPool, ChainGuesser $chainGuesser)
    {
        $this->projectDir = $projectDir;
        $this->urlGenerator = $urlGenerator;
        $this->cacheItemPool = $cacheItemPool;
        $this->chainGuesser = $chainGuesser;
    }

    public function getIcon(string $symbol): string
    {
        $cache = $this->cacheItemPool->getItem('icon-' . md5($symbol) . 'v' . $this->assetVersion);

        if ($cache->isHit()) {
            return $cache->get();
        }

        $icon = $this->getIconInner($symbol);

        $this->cacheItemPool->save($cache->set($icon)->expiresAfter(60 * 60 * 5 + random_int(1, 60)));

        return $icon;
    }

    private function getIconInner(string $symbol): string
    {
        $symbol = strtolower($symbol);

        if ($this->getLocalImage($symbol)) {
            return $this->urlGenerator->generate('token_icon', [
                'symbol' => $symbol,
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        }

        if (preg_match('#^(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getLocalPair($match[1], $match[2])) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getLocalPair($match[1], $match[2])) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getPair3($match[1], $match[2], $match[3])) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)-(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)-(\w+)-(\w+)\s+#i', $symbol,
                $match)) {
            if ($pair = $this->getPair4($match[1], $match[2], $match[3], $match[4])) {
                return $pair;
            }
        }

        return $this->urlGenerator->generate('token_icon', [
            'symbol' => 'unknown',
            'format' => 'png',
            'v' => $this->assetVersion
        ]);
    }

    public function getLocalPair(string $symbolA, string $symbolB): ?string
    {
        $symbolA = strtolower($symbolA);
        $symbolB = strtolower($symbolB);

        $iconA = $this->getLocalImage($symbolA);
        $iconB = $this->getLocalImage($symbolB);

        if (!$iconA & !$iconB) {
            return null;
        }

        return $this->urlGenerator->generate('token_icon_pair', [
            'symbolA' => file_exists($iconA) ? $symbolA : 'unknown',
            'symbolB' => file_exists($iconB) ? $symbolB : 'unknown',
            'format' => 'png',
            'v' => $this->assetVersion
        ]);
    }

    public function getPair3(string $symbolA, string $symbolB, string $symbolC): ?string
    {
        $symbolA = strtolower($symbolA);
        $symbolB = strtolower($symbolB);
        $symbolC = strtolower($symbolC);

        $iconA = $this->getLocalImage($symbolA);
        $iconB = $this->getLocalImage($symbolB);
        $iconC = $this->getLocalImage($symbolC);

        if (!$iconA & !$iconB & !$iconC) {
            return null;
        }

        return $this->urlGenerator->generate('token_icon_abc', [
            'symbolA' => file_exists($iconA) ? $symbolA : 'unknown',
            'symbolB' => file_exists($iconB) ? $symbolB : 'unknown',
            'symbolC' => file_exists($iconC) ? $symbolC : 'unknown',
            'format' => 'png',
            'v' => $this->assetVersion
        ]);
    }

    public function getPair4(string $symbolA, string $symbolB, string $symbolC, string $symbolD): ?string
    {
        $symbolA = strtolower($symbolA);
        $symbolB = strtolower($symbolB);
        $symbolC = strtolower($symbolC);
        $symbolD = strtolower($symbolD);

        $iconA = $this->getLocalImage($symbolA);
        $iconB = $this->getLocalImage($symbolB);
        $iconC = $this->getLocalImage($symbolC);
        $iconD = $this->getLocalImage($symbolD);

        if (!$iconA & !$iconB & !$iconC & !$iconD) {
            return null;
        }

        return $this->urlGenerator->generate('token_icon_abcd', [
            'symbolA' => file_exists($iconA) ? $symbolA : 'unknown',
            'symbolB' => file_exists($iconB) ? $symbolB : 'unknown',
            'symbolC' => file_exists($iconC) ? $symbolC : 'unknown',
            'symbolD' => file_exists($iconD) ? $symbolD : 'unknown',
            'format' => 'png',
            'v' => $this->assetVersion
        ]);
    }

    public function getLocalImage(string $symbol): ?string
    {
        if ($symbol === 'weth') {
            $symbol = 'eth';
        } else if ($symbol === 'wbtc' || $symbol === 'btcb') {
            $symbol = 'btc';
        } elseif ($symbol === 'wmatic') {
            $symbol = 'matic';
        } elseif ($symbol === 'wftm') {
            $symbol = 'ftm';
        } elseif ($symbol === 'wbnb') {
            $symbol = 'bnb';
        } elseif ($symbol === 'wone') {
            $symbol = 'one';
        }

        $filename = strtolower($symbol) . '.png';

        $icon = $this->projectDir . '/var/icons/' . $filename;
        if (file_exists($icon)) {
            return $icon;
        }

        $paths = [
            $this->projectDir . '/var/tokens/' . $this->chainGuesser->getChain() . '/symbol/',
            $this->projectDir . '/var/tokens/',
            $this->projectDir . '/remotes/cryptocurrency-icons/128/icon/'
        ];

        foreach ($paths as $path) {
            if (is_file($file = $path . $symbol . '.png')) {
                return $file;
            }
        }

        // prefixed "iBUSD", "beltUSD"
        foreach (['belt', 'i', 'ib', '1', 'bsc'] as $prefix) {
            if (str_starts_with(strtolower($symbol), $prefix)) {
                $symbol2 = substr($symbol, strlen($prefix));
                if (strlen($symbol2) >= 3 && $icon = $this->getLocalImage($symbol2)) {
                    return $icon;
                }
            }
        }

        return null;
    }

    public function getTokenIconForSymbolAddressReverse(string $icon): ?array
    {
        $cache = $this->cacheItemPool->getItem('icon-v1-addresses-' . md5($icon) . 'v' . $this->assetVersion);
        if ($cache->isHit()) {
            return $cache->get();
        }

        $files = [];

        $empty = true;
        foreach (explode('-', $icon) as $item) {
            if (str_starts_with($item, '0x')) {
                if (is_file($icon2 = ($this->projectDir . '/var/tokens/' . $this->chainGuesser->getChain() . '/address/' . strtolower($item) . '.png'))) {
                    $item = $icon2;
                    $empty = false;
                } else {
                    $item = $this->getLocalImage('unknown');
                }
            } else {
                if ($icon2 = $this->getLocalImage($item)) {
                    $item = $icon2;
                    $empty = false;
                } else {
                    $item = $this->getLocalImage('unknown');
                }
            }

            $files[] = $item;
        }

        $result = $empty ? null : $files;

        $this->cacheItemPool->save($cache->set($result)->expiresAfter(60 * 60 * 5 + random_int(1, 60)));

        return $result;
    }

    public function getTokenIconForSymbolAddress(array $tokens): string
    {
        $cache = $this->cacheItemPool->getItem('icon-v6-addresses-' . md5(json_encode($tokens)) . 'v' . $this->assetVersion);
        if ($cache->isHit()) {
            return $cache->get();
        }

        $parts = [];

        $empty = true;
        foreach ($tokens as $token) {
            if (isset($token['address']) && is_file($this->projectDir . '/var/tokens/' . $this->chainGuesser->getChain() . '/address/' . strtolower($token['address']) . '.png')) {
                $empty = false;
                $parts[] = $token['address'];
            } else if(isset($token['symbol']) && is_file($this->projectDir . '/var/tokens/' . $this->chainGuesser->getChain() . '/symbol/' . strtolower($token['symbol']) . '.png')) {
                $empty = false;
                $parts[] = strtolower($token['symbol']);
            } elseif (isset($token['symbol']) && is_file($this->projectDir . '/var/icons/' . strtolower($token['symbol']) . '.png')) {
                $empty = false;
                $parts[] = strtolower($token['symbol']);
            } elseif (isset($token['symbol']) && is_file($this->projectDir . '/var/tokens/' . strtolower($token['symbol']) . '.png')) {
                $empty = false;
                $parts[] = strtolower($token['symbol']);
            } else {
                $parts[] = 'unknown';
            }
        }

        $icon = null;
        if ($empty) {
            $icon = $this->urlGenerator->generate('token_icon', [
                'symbol' => 'unknown',
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        } elseif (count($parts) === 1) {
            $icon = $this->urlGenerator->generate('token_icon', [
                'symbol' => $parts[0],
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        } else if(count($parts) > 1) {
            $icon = $this->urlGenerator->generate('token_icon_pair', [
                'symbolA' => $parts[0],
                'symbolB' => $parts[1],
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        }

        $this->cacheItemPool->save($cache->set($icon)->expiresAfter(60 * 60 * 5 + random_int(1, 60)));

        return $icon;
    }
}