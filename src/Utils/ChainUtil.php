<?php

namespace App\Utils;

class ChainUtil
{
    public function getChainExplorerUrl(string $chain): string
    {
        if ($chain === 'bsc') {
            return 'https://bscscan.com';
        } else if($chain === 'polygon') {
            return 'https://polygonscan.com';
        } else if($chain === 'fantom') {
            return 'https://ftmscan.com';
        } else if($chain === 'kcc') {
            return 'https://explorer.kcc.io/en';
        } else if($chain === 'harmony') {
            return 'https://explorer.harmony.one';
        } elseif ($chain === 'celo') {
            return 'https://explorer.celo.org';
        } elseif ($chain === 'moonriver') {
            return 'https://blockscout.moonriver.moonbeam.network';
        } elseif ($chain === 'cronos') {
            return 'https://cronos.crypto.org/explorer';
        }

        return 'https://bscscan.com';
    }

    public static function getChains(): array
    {
        return [
            [
                'id' => 'bsc',
                'title' => 'Binance Smart Chain',
                'explorer' => 'https://bscscan.com',
                'chainId' => 56,
                'token' => 'bnb',
                'icon' => 'assets/chains/bsc.svg',
            ],
            [
                'id' => 'polygon',
                'title' => 'Polygon',
                'explorer' => 'https://polygonscan.com',
                'chainId' => 137,
                'token' => 'wmatic',
                'icon' => 'assets/chains/polygon.svg',
            ],
            [
                'id' => 'fantom',
                'title' => 'Fantom',
                'explorer' => 'https://ftmscan.com',
                'chainId' => 250,
                'token' => 'wftm',
                'icon' => 'assets/chains/fantom.svg',
            ],
            [
                'id' => 'kcc',
                'title' => 'KuCoin Community Chain',
                'explorer' => 'https://explorer.kcc.io/en',
                'chainId' => 321,
                'token' => 'kcs',
                'icon' => 'assets/chains/kcc.png',
            ],
            [
                'id' => 'harmony',
                'title' => 'Harmony',
                'explorer' => 'https://explorer.harmony.one',
                'chainId' => 1666600000,
                'token' => 'wone',
                'icon' => 'assets/chains/harmony.png',
            ],
            [
                'id' => 'celo',
                'title' => 'Celo',
                'explorer' => 'https://explorer.celo.org',
                'chainId' => 42220,
                'token' => 'celo',
                'icon' => 'assets/chains/celo.png',
            ],
            [
                'id' => 'moonriver',
                'title' => 'Moonriver',
                'explorer' => 'https://blockscout.moonriver.moonbeam.network',
                'chainId' => 1285,
                'token' => 'wmovr',
                'icon' => 'assets/chains/moonriver.png',
            ],
            [
                'id' => 'cronos',
                'title' => 'Crypto.com: Cronos',
                'explorer' => 'https://cronos.crypto.org/explorer',
                'chainId' => 25,
                'token' => 'wcro',
                'icon' => 'assets/chains/cronos.png',
            ]
        ];
    }

    public static function getChainOrNull(string $chainId): ?array
    {
        foreach (self::getChains() as $chain) {
            if ($chain['id'] === $chainId) {
                return $chain;
            }
        }

        return null;
    }

    public static function getChain(string $chainId): array
    {
        foreach(self::getChains() as $chain) {
            if ($chain['id'] === $chainId) {
                return $chain;
            }
        }

        throw new \InvalidArgumentException('Invalid chain:' . $chainId);
    }
}