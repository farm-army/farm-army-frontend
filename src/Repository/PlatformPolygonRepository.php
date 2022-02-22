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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'polygon',
        ];
    }

    public function getPlatformChunks(): array
    {
        $chunks = array_map(
            fn($p) => $p['id'],
            $this->getPlatforms()
        );

        $slowGroup = [];

        $chunks = array_diff($chunks, $slowGroup);

        $chunks = array_chunk($chunks, 5);

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
                'chain' => 'polygon',
                'group' => 'wault',
            ],
            [
                'id' => 'polycat',
                'label' => 'Polycat',
                'url' => 'https://polycat.finance/?ref=0k898r99681P29479o86304292o03071P80N57948S',
                'icon' => '/assets/platforms/polycat.png',
                'token' => 'fish',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pjetswap',
                'label' => 'JetSwap',
                'url' => 'https://polygon.jetswap.finance/',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'pwings',
                'chain' => 'polygon',
                'group' => 'jetswap',
            ],
            [
                'id' => 'augury',
                'label' => 'Augury',
                'url' => 'https://augury.finance/',
                'icon' => '/assets/platforms/augury.png',
                'token' => 'omen',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pswamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/polygon/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'pswamp',
                'chain' => 'polygon',
                'group' => 'swamp',
            ],
            [
                'id' => 'ppancakebunny',
                'label' => 'Bunny',
                'url' => 'https://polygon.pancakebunny.finance/',
                'icon' => '/assets/platforms/pancakebunny.png',
                'token' => 'polybunny',
                'chain' => 'polygon',
                'group' => 'pancakebunny',
            ],
            [
                'id' => 'mai',
                'label' => 'Mai.finance',
                'url' => 'https://app.mai.finance/farm',
                'icon' => '/assets/platforms/mai.png',
                'token' => 'qi',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pfarmhero',
                'label' => 'FarmHero',
                'url' => 'https://polygon.farmhero.io/?r=f3rm3rmy',
                'icon' => '/assets/platforms/farmhero.png',
                'token' => 'honor',
                'chain' => 'polygon',
                'group' => 'farmhero',
            ],
            [
                'id' => 'polycrystal',
                'label' => 'PolyCrystal',
                'url' => 'https://polycrystal.finance/',
                'icon' => '/assets/platforms/polycrystal.png',
                'token' => 'crystl',
                'chain' => 'polygon',
            ],
            [
                'id' => 'mstable',
                'label' => 'mStable',
                'url' => 'https://app.mstable.org/#/musd/save',
                'icon' => '/assets/platforms/mstable.png',
                'token' => 'mta',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/polygon',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'polygon',
                'group' => 'beefy',
            ],
            [
                'id' => 'dinoswap',
                'label' => 'DinoSwap',
                'url' => 'https://dinoswap.exchange/',
                'icon' => '/assets/platforms/dinoswap.png',
                'token' => 'dino',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/polygon',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'pauto',
                'chain' => 'polygon',
                'group' => 'autofarm',
            ],
            [
                'id' => 'dfyn',
                'label' => 'DFYN',
                'url' => 'https://exchange.dfyn.network/',
                'icon' => '/assets/platforms/dfyn.png',
                'token' => 'dfyn',
                'chain' => 'polygon',
            ],
            [
                'id' => 'papeswap',
                'label' => 'ApeSwap',
                'url' => 'https://app.apeswap.finance/',
                'icon' => '/assets/platforms/apeswap.png',
                'token' => 'banana',
                'chain' => 'polygon',
                'group' => 'apeswap',
            ],
            [
                'id' => 'psushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
                'chain' => 'polygon',
                'group' => 'psushi',
            ],
            [
                'id' => 'pcurve',
                'label' => 'Curve',
                'url' => 'https://polygon.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'crv',
                'chain' => 'polygon',
                'group' => 'pcurve',
            ],
            [
                'id' => 'peleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
                'chain' => 'polygon',
                'group' => 'eleven',
            ],
            [
                'id' => 'adamant',
                'label' => 'Adamant',
                'url' => 'https://adamant.finance',
                'icon' => '/assets/platforms/adamant.png',
                'token' => 'addy',
                'chain' => 'polygon',
            ],
            [
                'id' => 'quickswap',
                'label' => 'QuickSwap',
                'url' => 'https://quickswap.exchange/#/quick',
                'icon' => '/assets/platforms/quickswap.png',
                'token' => 'quick',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pcream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
                'chain' => 'polygon',
                'group' => 'cream',
            ],
            [
                'id' => 'pfortube',
                'label' => 'ForTube',
                'url' => 'https://for.tube/market/index',
                'icon' => '/assets/platforms/fortube.png',
                'token' => 'for',
                'chain' => 'polygon',
                'group' => 'fortube',
            ],
            [
                'id' => 'balancer',
                'label' => 'Balancer',
                'url' => 'https://polygon.balancer.fi/',
                'icon' => '/assets/platforms/balancer.png',
                'token' => 'bal',
                'chain' => 'polygon',
            ],
            [
                'id' => 'impermax',
                'label' => 'Impermax',
                'url' => 'https://polygon.impermax.finance/',
                'icon' => '/assets/platforms/impermax.png',
                'token' => 'imx',
                'chain' => 'polygon',
            ],
            [
                'id' => 'pcafeswap',
                'label' => 'Cafeswap',
                'url' => 'https://polygon.cafeswap.finance/',
                'icon' => '/assets/platforms/cafeswap.png',
                'token' => 'pbrew',
                'chain' => 'polygon',
                'group' => 'cafeswap',
            ],
            [
                'id' => 'polysage',
                'label' => 'PolySage',
                'url' => 'https://www.polysage.finance/',
                'icon' => '/assets/platforms/polysage.png',
                'token' => 'sage',
                'chain' => 'polygon',
            ],
            [
                'id' => 'paave',
                'label' => 'Aave',
                'url' => 'https://app.aave.com/#/markets',
                'icon' => '/assets/platforms/aave.png',
                'token' => 'aave',
                'chain' => 'polygon',
            ],
            [
                'id' => 'psynapse',
                'label' => 'Synapse',
                'url' => 'https://synapseprotocol.com/pools',
                'icon' => '/assets/platforms/synapse.png',
                'token' => 'syn',
                'chain' => 'polygon',
                'group' => 'synapse',
            ],
            [
                'id' => 'market',
                'label' => 'Market',
                'url' => 'https://polygon.market.xyz/',
                'icon' => '/assets/platforms/market.png',
                'chain' => 'polygon',
                'group' => 'market',
            ],
            [
                'id' => 'patlantis',
                'label' => 'Atlantis',
                'url' => 'https://atlantis.loans/app',
                'icon' => '/assets/platforms/atlantis.png',
                'token' => 'atl',
                'chain' => 'polygon',
                'group' => 'atlantis',
            ],
            [
                'id' => 'uniswap',
                'label' => 'Uniswap',
                'url' => 'https://app.uniswap.org',
                'icon' => '/assets/platforms/uniswap.png',
                'token' => 'uni',
                'chain' => 'polygon',
            ],
        ];
    }
}