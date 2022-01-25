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

    public function getIcon(string $symbol, string $chain = null): string
    {
        $cache = $this->cacheItemPool->getItem('icon-' . md5($symbol . $chain) . 'v' . $this->assetVersion);

        if ($cache->isHit()) {
            return $cache->get();
        }

        if (!$chain) {
            $chain = $this->chainGuesser->getChain();
        }

        $icon = $this->getIconInner($symbol, $chain);

        $this->cacheItemPool->save($cache->set($icon)->expiresAfter(60 * 60 * 5 + random_int(1, 60)));

        return $icon;
    }

    private function getIconInner(string $symbol, string $chain): string
    {
        $symbol = strtolower($symbol);

        if ($result = $this->getLocalImage($symbol, $chain)) {
            return $this->urlGenerator->generate('chain_token_icon', [
                'symbol' => pathinfo(basename($result), PATHINFO_FILENAME),
                'format' => 'png',
                'chain' => $chain,
                'v' => $this->assetVersion
            ]);
        }

        if (preg_match('#^(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getPair2($match[1], $match[2], $chain)) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getPair2($match[1], $match[2], $chain)) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getPair3($match[1], $match[2], $match[3], $chain)) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)-(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)-(\w+)-(\w+)\s+#i', $symbol,
                $match)) {
            if ($pair = $this->getPair4($match[1], $match[2], $match[3], $match[4], $chain)) {
                return $pair;
            }
        }

        if (preg_match('#^(\w+)-(\w+)-(\w+)-(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)-(\w+)-(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if ($pair = $this->getPair5($match[1], $match[2], $match[3], $match[4], $match[5], $chain)) {
                return $pair;
            }
        }

        return $this->urlGenerator->generate('chain_token_icon', [
            'symbol' => 'unknown',
            'format' => 'png',
            'chain' => $chain,
            'v' => $this->assetVersion
        ]);
    }

    private function getPair2(string $symbolA, string $symbolB, string $chain): ?string
    {
        $symbolA = strtolower($symbolA);
        $symbolB = strtolower($symbolB);

        $iconA = $this->getLocalImage($symbolA);
        $iconB = $this->getLocalImage($symbolB);

        if (!$iconA & !$iconB) {
            return null;
        }

        return $this->urlGenerator->generate('chain_token_icon_pair', [
            'symbolA' => file_exists($iconA) ? pathinfo(basename($iconA), PATHINFO_FILENAME) : 'unknown',
            'symbolB' => file_exists($iconB) ? pathinfo(basename($iconB), PATHINFO_FILENAME) : 'unknown',
            'format' => 'png',
            'chain' => $chain,
            'v' => $this->assetVersion
        ]);
    }

    private function getPair3(string $symbolA, string $symbolB, string $symbolC, string $chain): ?string
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

        return $this->urlGenerator->generate('chain_token_icon_abc', [
            'symbolA' => file_exists($iconA) ? pathinfo(basename($iconA), PATHINFO_FILENAME) : 'unknown',
            'symbolB' => file_exists($iconB) ? pathinfo(basename($iconB), PATHINFO_FILENAME) : 'unknown',
            'symbolC' => file_exists($iconC) ? pathinfo(basename($iconC), PATHINFO_FILENAME) : 'unknown',
            'format' => 'png',
            'chain' => $chain,
            'v' => $this->assetVersion
        ]);
    }

    private function getPair4(string $symbolA, string $symbolB, string $symbolC, string $symbolD, string $chain): ?string
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

        return $this->urlGenerator->generate('chain_token_icon_abcd', [
            'symbolA' => file_exists($iconA) ? pathinfo(basename($iconA), PATHINFO_FILENAME) : 'unknown',
            'symbolB' => file_exists($iconB) ? pathinfo(basename($iconB), PATHINFO_FILENAME) : 'unknown',
            'symbolC' => file_exists($iconC) ? pathinfo(basename($iconC), PATHINFO_FILENAME) : 'unknown',
            'symbolD' => file_exists($iconD) ? pathinfo(basename($iconD), PATHINFO_FILENAME) : 'unknown',
            'format' => 'png',
            'chain' => $chain,
            'v' => $this->assetVersion
        ]);
    }

    private function getPair5(string $symbolA, string $symbolB, string $symbolC, string $symbolD, string $symbolE, string $chain): ?string
    {
        $symbolA = strtolower($symbolA);
        $symbolB = strtolower($symbolB);
        $symbolC = strtolower($symbolC);
        $symbolD = strtolower($symbolD);
        $symbolE = strtolower($symbolE);

        $iconA = $this->getLocalImage($symbolA);
        $iconB = $this->getLocalImage($symbolB);
        $iconC = $this->getLocalImage($symbolC);
        $iconD = $this->getLocalImage($symbolD);
        $iconE = $this->getLocalImage($symbolE);

        if (!$iconA && !$iconB && !$iconC && !$iconD && !$iconE) {
            return null;
        }

        return $this->urlGenerator->generate('chain_token_icon_abcde', [
            'symbolA' => file_exists($iconA) ? pathinfo(basename($iconA), PATHINFO_FILENAME) : 'unknown',
            'symbolB' => file_exists($iconB) ? pathinfo(basename($iconB), PATHINFO_FILENAME) : 'unknown',
            'symbolC' => file_exists($iconC) ? pathinfo(basename($iconC), PATHINFO_FILENAME) : 'unknown',
            'symbolD' => file_exists($iconD) ? pathinfo(basename($iconD), PATHINFO_FILENAME) : 'unknown',
            'symbolE' => file_exists($iconE) ? pathinfo(basename($iconE), PATHINFO_FILENAME) : 'unknown',
            'format' => 'png',
            'chain' => $chain,
            'v' => $this->assetVersion
        ]);
    }

    public function getLocalImage(string $symbol, ?string $chain = null): ?string
    {
        $symbol = $this->getSymbolNormalized($symbol);

        $filename = strtolower($symbol) . '.png';

        $icon = $this->projectDir . '/var/icons/' . $filename;
        if (file_exists($icon)) {
            return $icon;
        }

        if (!$chain) {
            $chain = $this->chainGuesser->getChain();
        }

        $paths = [
            $this->projectDir . '/var/tokens/' . $chain . '/symbol/',
            $this->projectDir . '/var/tokens/',
            $this->projectDir . '/remotes/cryptocurrency-icons/128/icon/'
        ];

        foreach ($paths as $path) {
            if (is_file($file = $path . $symbol . '.png')) {
                return $file;
            }
        }

        // prefixed "iBUSD", "beltUSD"
        foreach (['belt', 'i', 'ib', '1', 'bsc', 'ele'] as $prefix) {
            if (str_starts_with(strtolower($symbol), $prefix)) {
                $symbol2 = substr($symbol, strlen($prefix));
                if (strlen($symbol2) >= 3 && $icon = $this->getLocalImage($symbol2, $chain)) {
                    return $icon;
                }
            }
        }

        // prefixed "iBUSD", "beltUSD"
        foreach (['.m'] as $suffix) {
            if (str_ends_with(strtolower($symbol), $suffix)) {
                $symbol2 = substr($symbol, 0, -2);
                if (strlen($symbol2) >= 3 && $icon = $this->getLocalImage($symbol2, $chain)) {
                    return $icon;
                }
            }
        }

        return null;
    }

    public function getTokenIconForSymbolAddressReverse(string $icon, ?string $chain = null): ?array
    {
        if (!$chain) {
            $chain = $this->chainGuesser->getChain();
        }

        $cache = $this->cacheItemPool->getItem('icon-v1-addresses-' . md5($icon . $chain) . 'v' . $this->assetVersion);
        if ($cache->isHit()) {
            return $cache->get();
        }

        $files = [];

        $empty = true;
        foreach (explode('-', $icon) as $item) {
            if (str_starts_with($item, '0x')) {
                if (is_file($icon2 = ($this->projectDir . '/var/tokens/' . $chain . '/address/' . strtolower($item) . '.png'))) {
                    $item = $icon2;
                    $empty = false;
                } else {
                    $item = $this->getLocalImage('unknown', $chain);
                }
            } else {
                if ($icon2 = $this->getLocalImage($item, $chain)) {
                    $item = $icon2;
                    $empty = false;
                } else {
                    $item = $this->getLocalImage('unknown', $chain);
                }
            }

            $files[] = $item;
        }

        $result = $empty ? null : $files;

        $this->cacheItemPool->save($cache->set($result)->expiresAfter((60 * 60 * 5) + random_int(10, 60)));

        return $result;
    }

    public function getTokenIconForSymbolAddress(string $chain, array $tokens): string
    {
        $cache = $this->cacheItemPool->getItem('icon-v10-addresses-' . md5($chain . json_encode($tokens)) . 'v' . $this->assetVersion);
        if ($cache->isHit()) {
            return $cache->get();
        }

        $parts = [];

        $empty = true;
        foreach ($tokens as $token) {
            if (isset($token['address']) && is_file($this->projectDir . '/var/tokens/' . $this->chainGuesser->getChain() . '/address/' . strtolower($token['address']) . '.png')) {
                $empty = false;
                $parts[] = $token['address'];
            } elseif (isset($token['symbol']) && $image = $this->getLocalImage(strtolower($token['symbol']))) {
                $empty = false;
                $parts[] = pathinfo($image, PATHINFO_FILENAME);;
            } else {
                $parts[] = 'unknown';
            }
        }

        $icon = null;
        if ($empty) {
            $icon = $this->urlGenerator->generate('chain_token_icon', [
                'symbol' => 'unknown',
                'format' => 'png',
                'chain' => $chain,
                'v' => $this->assetVersion
            ]);
        } elseif (count($parts) === 1) {
            $icon = $this->urlGenerator->generate('chain_token_icon', [
                'symbol' => $parts[0],
                'format' => 'png',
                'chain' => $chain,
                'v' => $this->assetVersion
            ]);
        } else if(count($parts) > 1) {
            $icon = $this->urlGenerator->generate('chain_token_icon_pair', [
                'symbolA' => $parts[0],
                'symbolB' => $parts[1],
                'chain' => $chain,
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        }

        $this->cacheItemPool->save($cache->set($icon)->expiresAfter(60 * 60 * 5 + random_int(1, 60)));

        return $icon;
    }

    private function getSymbolNormalized(string $symbol): string
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
        } elseif ($symbol === 'wmovr') {
            $symbol = 'movr';
        } elseif ($symbol === 'wcro') {
            $symbol = 'cro';
        } elseif ($symbol === 'wglmr') {
            $symbol = 'glmr';
        }

        return $symbol;
    }
}