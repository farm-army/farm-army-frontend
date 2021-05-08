<?php

namespace App\Symbol;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IconResolver
{
    private const STATIC = [];

    private string $projectDir;
    private UrlGeneratorInterface $urlGenerator;
    private CacheItemPoolInterface $cacheItemPool;
    private TokenResolver $tokenResolver;
    private int $assetVersion = 3;

    public function __construct(string $projectDir, UrlGeneratorInterface $urlGenerator, CacheItemPoolInterface $cacheItemPool, TokenResolver $tokenResolver)
    {
        $this->projectDir = $projectDir;
        $this->urlGenerator = $urlGenerator;
        $this->cacheItemPool = $cacheItemPool;
        $this->tokenResolver = $tokenResolver;
    }

    public function getIcon(string $symbol): string
    {
        $cache = $this->cacheItemPool->getItem('icon-' . md5($symbol));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $icon = $this->getIconInner($symbol);

        $this->cacheItemPool->save($cache->set($icon)->expiresAfter(60 * 24 * 5));

        return $icon;
    }

    private function getIconInner(string $symbol): string
    {
        $symbol = strtolower($symbol);

        if ($local = $this->getLocalImage($symbol)) {
            return $this->urlGenerator->generate('token_icon', [
                'symbol' => $symbol,
                'format' => 'png',
                'v' => $this->assetVersion
            ]);
        }

        if (isset(self::STATIC[$symbol])) {
            return self::STATIC[$symbol];
        }

        if (preg_match('#^(\w+)-(\w+)$#i', $symbol, $match) || preg_match('#(\w+)-(\w+)\s+#i', $symbol, $match)) {
            if (isset(self::STATIC[$match[1] . '-' . $match[2]])) {
                return self::STATIC[$match[1] . '-' . $match[2]];
            }

            if (isset(self::STATIC[$match[2] . '-' . $match[1]])) {
                return self::STATIC[$match[2] . '-' . $match[1]];
            }

            if ($pair = $this->getLocalPair($match[1], $match[2])) {
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

        if (!file_exists($iconA) & !file_exists($iconB)) {
            return null;
        }

        return $this->urlGenerator->generate('token_icon_pair', [
            'symbolA' => file_exists($iconA) ? $symbolA : 'unknown',
            'symbolB' => file_exists($iconB) ? $symbolB : 'unknown',
            'format' => 'png',
            'v' => $this->assetVersion
        ]);
    }

    public function getLocalImage(string $symbol): ?string
    {
        $filename = strtolower($symbol) . '.png';

        $icon = $this->projectDir . '/var/icons/' . $filename;
        if (file_exists($icon)) {
            return $icon;
        }

        $paths = [
            $this->projectDir . '/remotes/pancake-frontend/public/images/tokens/',
            $this->projectDir . '/remotes/cryptocurrency-icons/128/icon'
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            if (is_file($file = $path . $symbol . '.png')) {
                return $file;
            }

            if (is_file($file = $path . strtoupper($symbol) . '.png')) {
                return $file;
            }
        }

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $finder = new Finder();
            $finder->name('*.png');

            foreach ($finder->in($path) as $file) {
                $fileName = strtolower($file->getFilename());

                if ($fileName === $symbol . '.png') {
                    return $file->getPathname();
                };
            }
        }

        if ($asset = $this->tokenResolver->getTokenIcon($symbol)) {
            return $asset;
        }

        // prefixed "iBUSD", "beltUSD"
        foreach (['belt', 'i', 'ib'] as $prefix) {
            if (str_starts_with(strtolower($symbol), $prefix)) {
                $symbol2 = substr($symbol, strlen($prefix));
                if (strlen($symbol2) >= 3 && $icon = $this->getLocalImage($symbol2)) {
                    return $icon;
                }
            }
        }

        return null;
    }
}