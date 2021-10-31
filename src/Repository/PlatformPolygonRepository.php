<?php declare(strict_types=1);

namespace App\Repository;

class PlatformPolygonRepository implements PlatformRepositoryInterface
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
                'id' => 'pwault',
                'label' => 'Wault Finance',
                'url' => 'https://app.wault.finance/',
                'icon' => '/assets/platforms/wault.png',
                'token' => 'wexpoly',
            ],
            [
                'id' => 'polyzap',
                'label' => 'Polyzap',
                'url' => 'https://farm.polyzap.finance?ref=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/polyzap.png',
                'token' => 'pzap',
            ],
            [
                'id' => 'polycat',
                'label' => 'Polycat',
                'url' => 'https://polycat.finance/?ref=0k898r99681P29479o86304292o03071P80N57948S',
                'icon' => '/assets/platforms/polycat.png',
                'token' => 'fish',
            ],
            [
                'id' => 'pjetswap',
                'label' => 'JetSwap',
                'url' => 'https://polygon.jetswap.finance/',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'pwings',
            ],
            [
                'id' => 'augury',
                'label' => 'Augury',
                'url' => 'https://augury.finance/',
                'icon' => '/assets/platforms/augury.png',
                'token' => 'omen',
            ],
            [
                'id' => 'pswamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/polygon/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'pswamp',
            ],
            [
                'id' => 'ppancakebunny',
                'label' => 'Bunny',
                'url' => 'https://polygon.pancakebunny.finance/',
                'icon' => '/assets/platforms/pancakebunny.png',
                'token' => 'polybunny',
            ],
            [
                'id' => 'mai',
                'label' => 'Mai.finance',
                'url' => 'https://app.mai.finance/farm',
                'icon' => '/assets/platforms/mai.png',
                'token' => 'qi',
            ],
            [
                'id' => 'pfarmhero',
                'label' => 'FarmHero',
                'url' => 'https://polygon.farmhero.io/?r=f3rm3rmy',
                'icon' => '/assets/platforms/farmhero.png',
                'token' => 'honor',
            ],
            [
                'id' => 'polycrystal',
                'label' => 'PolyCrystal',
                'url' => 'https://polycrystal.finance/',
                'icon' => '/assets/platforms/polycrystal.png',
                'token' => 'crystl',
            ],
            [
                'id' => 'mstable',
                'label' => 'mStable',
                'url' => 'https://app.mstable.org/#/musd/save',
                'icon' => '/assets/platforms/mstable.png',
                'token' => 'mta',
            ],
            [
                'id' => 'pbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/polygon',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'dinoswap',
                'label' => 'DinoSwap',
                'url' => 'https://dinoswap.exchange/',
                'icon' => '/assets/platforms/dinoswap.png',
                'token' => 'dino',
            ],
            [
                'id' => 'pautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/polygon',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'pauto',
            ],
            [
                'id' => 'dfyn',
                'label' => 'DFYN',
                'url' => 'https://exchange.dfyn.network/',
                'icon' => '/assets/platforms/dfyn.png',
                'token' => 'dfyn',
            ],
            [
                'id' => 'papeswap',
                'label' => 'ApeSwap',
                'url' => 'https://app.apeswap.finance/',
                'icon' => '/assets/platforms/apeswap.png',
                'token' => 'banana',
            ],
            [
                'id' => 'psushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
            ],
            [
                'id' => 'pcurve',
                'label' => 'Curve',
                'url' => 'https://polygon.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'crv',
            ],
            [
                'id' => 'peleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
            ],
            [
                'id' => 'adamant',
                'label' => 'Adamant',
                'url' => 'https://adamant.finance',
                'icon' => '/assets/platforms/adamant.png',
                'token' => 'addy',
            ],
            [
                'id' => 'quickswap',
                'label' => 'QuickSwap',
                'url' => 'https://quickswap.exchange/#/quick',
                'icon' => '/assets/platforms/quickswap.png',
                'token' => 'quick',
            ],
            [
                'id' => 'pearzap',
                'label' => 'PearZap',
                'url' => 'https://app.pearzap.com/?r=yv676c7746zC072579641y20709y1y5zC6yA35726F',
                'icon' => '/assets/platforms/pearzap.png',
                'token' => 'pear',
            ],
            [
                'id' => 'pcream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
            ],
            [
                'id' => 'pfortube',
                'label' => 'ForTube',
                'url' => 'https://for.tube/market/index',
                'icon' => '/assets/platforms/fortube.png',
                'token' => 'for',
            ],
            [
                'id' => 'balancer',
                'label' => 'Balancer',
                'url' => 'https://polygon.balancer.fi/',
                'icon' => '/assets/platforms/balancer.png',
                'token' => 'bal',
            ],
            [
                'id' => 'impermax',
                'label' => 'Impermax',
                'url' => 'https://polygon.impermax.finance/',
                'icon' => '/assets/platforms/impermax.png',
                'token' => 'imx',
            ],
            [
                'id' => 'pcafeswap',
                'label' => 'Cafeswap',
                'url' => 'https://polygon.cafeswap.finance/',
                'icon' => '/assets/platforms/cafeswap.png',
                'token' => 'pbrew',
            ],
            [
                'id' => 'polysage',
                'label' => 'PolySage',
                'url' => 'https://www.polysage.finance/',
                'icon' => '/assets/platforms/polysage.png',
                'token' => 'sage',
            ],
            [
                'id' => 'paave',
                'label' => 'Aave',
                'url' => 'https://app.aave.com/#/markets',
                'icon' => '/assets/platforms/aave.png',
                'token' => 'aave',
            ],
            [
                'id' => 'pfulcrum',
                'label' => 'Fulcrum',
                'url' => 'https://polygon.fulcrum.trade/lend',
                'icon' => '/assets/platforms/fulcrum.png',
                'token' => 'bzrk',
            ],
        ];
    }
}