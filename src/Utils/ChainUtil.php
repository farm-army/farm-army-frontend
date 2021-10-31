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
        }

        return 'https://bscscan.com';
    }

    public static function getChain(string $chain): array
    {
        switch ($chain) {
            case 'bsc':
                return [
                    'id' => $chain,
                    'title' => 'Binance Smart Chain',
                    'explorer' => 'https://bscscan.com',
                    'chainId' => 56,
                    'token' => 'bnb',
                ];
            case 'polygon':
                return [
                    'id' => $chain,
                    'title' => 'Polygon',
                    'explorer' => 'https://polygonscan.com',
                    'chainId' => 137,
                    'token' => 'wmatic',
                ];
            case 'fantom':
                return [
                    'id' => $chain,
                    'title' => 'Fantom',
                    'explorer' => 'https://ftmscan.com',
                    'chainId' => 250,
                    'token' => 'wftm',
                ];
            case 'kcc':
                return [
                    'id' => $chain,
                    'title' => 'KuCoin Community Chain',
                    'explorer' => 'https://explorer.kcc.io/en',
                    'chainId' => 321,
                    'token' => 'kcs',
                ];
            case 'harmony':
                return [
                    'id' => $chain,
                    'title' => 'Harmony',
                    'explorer' => 'https://explorer.harmony.one',
                    'chainId' => 1666600000,
                    'token' => 'wone',
                ];
            case 'celo':
                return [
                    'id' => $chain,
                    'title' => 'Celo',
                    'explorer' => 'https://explorer.celo.org',
                    'chainId' => 42220,
                    'token' => 'celo',
                ];
            case 'moonriver':
                return [
                    'id' => $chain,
                    'title' => 'Moonriver',
                    'explorer' => 'https://blockscout.moonriver.moonbeam.network',
                    'chainId' => 1285,
                    'token' => 'wmovr',
                ];
        }

        throw new \InvalidArgumentException('Invalid chain:' . $chain);
    }
}