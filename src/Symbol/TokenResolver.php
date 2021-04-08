<?php declare(strict_types=1);

namespace App\Symbol;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Finder\Finder;

class TokenResolver
{
    private $tokenMap = false;

    public function __construct(string $projectDir, CacheItemPoolInterface $cacheItemPool)
    {
        $this->projectDir = $projectDir;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function getTokenIcon(string $symbol): ?string
    {
        if ($this->tokenMap === false) {
            $this->tokenMap = $this->getTokenIconMap();
        }

        return $this->tokenMap[strtolower($symbol)] ?? null;
    }

    private function getTokenMap(): array
    {
        return [
            '0x7b65B489fE53fCE1F6548Db886C08aD73111DDd8' => 'iron',
            '0xd72aA9e1cDDC2F6D6e0444580002170fbA1f8eED' => 'mda',
            '0xC1eDCc306E6faab9dA629efCa48670BE4678779D' => 'mdg',
            '0xeFb94d158206dfa5CB8c30950001713106440928' => 'seeds',
            '0xc66E4De0d9b4F3CB3f271c37991fE62f154471EB' => 'sil',
            '0x0610C2d9F6EbC40078cf081e2D1C4252dD50ad15' => 'vbswap',
            '0x35e869B7456462b81cdB5e6e42434bD27f3F788c' => 'mdo',
            '0xc2161d47011C4065648ab9cDFd0071094228fa09' => 'bcash',
        ];
    }

    private function getTokenIconMap(): array
    {
        $cache = $this->cacheItemPool->getItem('icon-map');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $dirs = [
            $this->projectDir . '/remotes/valuedefi-trustwallet-assets/blockchains/smartchain/assets',
            $this->projectDir . '/remotes/trustwallet-assets/blockchains/smartchain/assets',
        ];

        $finder = new Finder();
        $finder->name('0x*');

        $tokens = [];

        $tokenMap = $this->getTokenMap();

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach ($finder->in($dir)->directories() as $directory) {
                $info = $directory->getPathname() . '/info.json';
                if (!is_file($info)) {
                    continue;
                }

                $icon = $directory->getPathname() . '/logo.png';
                if (!is_file($icon)) {
                    continue;
                }

                $token = $directory->getBasename();

                if (isset($tokens[$token])) {
                    continue;
                }

                $symbol = null;
                if (($decode = json_decode(file_get_contents($info), true)) && isset($decode['symbol'])) {
                    $symbol = $decode['symbol'];
                } else if(isset($tokenMap[$token])) {
                    $symbol = $tokenMap[$token];
                }

                if (!$symbol) {
                    continue;
                }

                $tokens[$token] = [
                    'symbol' => strtolower($symbol),
                    'address' => $token,
                    'icon' => $icon,
                ];
            }
        }

        $icons = [];
        foreach ($tokens as $token) {
            if (isset($token['symbol'])) {
                $icons[$token['symbol']] = $token['icon'];
            }

            $icons[$token['address']] = $token['icon'];
        }

        $this->cacheItemPool->save($cache->set($icons)->expiresAfter(60 * 60 * 24 * 7));

        return $icons;
    }
}