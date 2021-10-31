<?php declare(strict_types=1);

namespace App\Repository;

class PlatformFantomRepository implements PlatformRepositoryInterface
{
    public function getPlatform(string $id): array
    {
        foreach($this->getPlatforms() as $platform) {
            if ($platform['id'] === $id) {
                return $platform;
            }
        }

        return [
            'id' => 'unknown',
            'label' => 'Unknown',
            'icon' => '/assets/platforms/unknown.png',
        ];
    }

    public function getPlatformChunks(): array
    {
        $chunks = array_map(
            fn($p) => $p['id'],
            $this->getPlatforms()
        );

        // $middleGroup = ['pancakebunny'];
        $slowGroup = [];

        $chunks = array_diff($chunks, $slowGroup);
        // $chunks = array_diff($chunks, $middleGroup);

        $chunks = array_chunk($chunks, 5);

        //$chunks[] = $slowGroup;
        // $chunks[] = $middleGroup;

        return $chunks;
    }

    /**
     * @return \string[][]
     */
    public function getPlatforms(): array
    {
        return [
            [
                'id' => 'spiritswap',
                'label' => 'SpiritSwap',
                'url' => 'https://app.spiritswap.finance/',
                'icon' => '/assets/platforms/spiritswap.png',
                'token' => 'spirit',
            ],
            [
                'id' => 'spookyswap',
                'label' => 'SpookySwap',
                'url' => 'https://spookyswap.finance/',
                'icon' => '/assets/platforms/spookyswap.png',
                'token' => 'boo',
            ],
            [
                'id' => 'liquiddriver',
                'label' => 'LiquidDriver',
                'url' => 'https://www.liquiddriver.finance/',
                'icon' => '/assets/platforms/liquiddriver.png',
                'token' => 'lqdr',
            ],
            [
                'id' => 'fbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/fantom',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'fcurve',
                'label' => 'Curve',
                'url' => 'https://ftm.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'crv',
            ],
            [
                'id' => 'frankenstein',
                'label' => 'Frankenstein',
                'url' => 'https://frankenstein.finance/?ref=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/frankenstein.png',
                'token' => 'frank',
            ],
            [
                'id' => 'ester',
                'label' => 'Ester',
                'url' => 'https://app.ester.finance/',
                'icon' => '/assets/platforms/ester.png',
                'token' => 'est',
            ],
            [
                'id' => 'reaper',
                'label' => 'Reaper',
                'url' => 'https://www.reaper.farm/',
                'icon' => '/assets/platforms/reaper.png',
                'token' => 'unknown',
            ],
            [
                'id' => 'fcream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
            ],
            [
                'id' => 'scream',
                'label' => 'Scream',
                'url' => 'https://scream.sh/',
                'icon' => '/assets/platforms/scream.png',
                'token' => 'scream',
            ],
            [
                'id' => 'tarot',
                'label' => 'Tarot',
                'url' => 'https://www.tarot.to/',
                'icon' => '/assets/platforms/tarot.png',
                'token' => 'tarot',
            ],
            [
                'id' => 'fwaka',
                'label' => 'Waka',
                'url' => 'https://waka.finance/',
                'icon' => '/assets/platforms/waka.png',
                'token' => 'waka',
            ],
            [
                'id' => 'fhyperjump',
                'label' => 'HyperJump',
                'url' => 'https://ftm.hyperjump.app/farms',
                'icon' => '/assets/platforms/hyperjump.png',
                'token' => 'ori',
            ],
            [
                'id' => 'fautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/fantom/',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'fauto',
            ],
            [
                'id' => 'feleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
            ],
            [
                'id' => 'fjetswap',
                'label' => 'JetSwap',
                'url' => 'https://fantom.jetswap.finance/',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'fwings',
            ],
            [
                'id' => 'paintswap',
                'label' => 'PaintSwap',
                'url' => 'https://paintswap.finance/',
                'icon' => '/assets/platforms/paintswap.png',
                'token' => 'brush',
            ],
            [
                'id' => 'fswamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/fantom/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'fswamp',
            ],
            [
                'id' => 'beethovenx',
                'label' => 'Beethovenx',
                'url' => 'https://app.beethovenx.io/#/',
                'icon' => '/assets/platforms/beethovenx.png',
                'token' => 'beets',
            ],
            [
                'id' => 'robovault',
                'label' => 'RoboVault',
                'url' => 'https://www.robo-vault.com/',
                'icon' => '/assets/platforms/robovault.png',
            ],
            [
                'id' => 'morpheus',
                'label' => 'Morpheus',
                'url' => 'https://morpheusswap.app/',
                'icon' => '/assets/platforms/morpheus.png',
                'token' => 'morph',
            ],
            [
                'id' => 'geist',
                'label' => 'Geist',
                'url' => 'https://geist.finance/markets',
                'icon' => '/assets/platforms/geist.png',
                'token' => 'geist',
            ],
            [
                'id' => 'grim',
                'label' => 'Grim',
                'url' => 'https://app.grim.finance/',
                'icon' => '/assets/platforms/grim.png',
            ],
            [
                'id' => 'zoocoin',
                'label' => 'Zoocoin',
                'url' => 'https://dex.zoocoin.cash/',
                'icon' => '/assets/platforms/zoocoin.png',
            ],
            [
                'id' => 'fpearzap',
                'label' => 'PearZap',
                'url' => 'https://fantom.pearzap.com/?r=yv676c7746zC072579641y20709y1y5zC6yA35726F',
                'icon' => '/assets/platforms/pearzap.png',
            ],
        ];
    }
}