<?php declare(strict_types=1);

namespace App\Symbol;

use Symfony\Component\Finder\Finder;

class TokenResolver
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    private function getTokenMap(string $chain): array
    {
        if ($chain === 'bsc') {
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

        return [];
    }

    public function getChainTokens(string $chain): array
    {
        switch ($chain) {
            case 'bsc':
                $dirs = [
                    $this->projectDir . '/remotes/valuedefi-trustwallet-assets/blockchains/smartchain/assets',
                    $this->projectDir . '/remotes/trustwallet-assets/blockchains/smartchain/assets',
                ];
                break;
            case 'polygon':
                $dirs = [];
                break;
            case 'fantom':
                $dirs = [];
                break;
            case 'kcc':
                $dirs = [];
            case 'harmony':
                $dirs = [];
                break;
            case 'celo':
                $dirs = [];
                break;
            case 'moonriver':
                $dirs = [];
                break;
            default:
                throw new \InvalidArgumentException('Invalid chain ' . $chain);
        }

        $finder = new Finder();
        $finder->depth('== 0')->name('0x*');

        $tokens = [];

        $tokenMap = $this->getTokenMap($chain);

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

        return array_values($tokens);
    }

    public function getPancakeTokens(): array
    {
        $content = file_get_contents($this->projectDir . '/remotes/pancake-frontend/src/config/constants/tokens.ts');

        preg_match_all(
            "#symbol:\s*'(?<symbol>\w+)',\s*address:\s*{\s*56:\s*'(?<address>\w+)'#m",
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $tokens = [];

        foreach ($matches as $match) {
            $address = strtolower($match['address']);

            $tokens[$address] = [
                'symbol' => strtolower($match['symbol']),
                'address' => $address,
            ];
        }

        return $tokens;
    }
}