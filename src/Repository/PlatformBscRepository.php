<?php declare(strict_types=1);

namespace App\Repository;

class PlatformBscRepository
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
        $slowGroup = ['alpha', 'alpaca'];

        $chunks = array_diff($chunks, $slowGroup);
       // $chunks = array_diff($chunks, $middleGroup);

        $chunks = array_chunk($chunks, 5);

        $chunks[] = $slowGroup;
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
                'id' => 'autofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'auto',
            ],
            [
                'id' => 'beefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/bsc',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'pancakebunny',
                'label' => 'Bunny',
                'url' => 'https://pancakebunny.finance/',
                'icon' => '/assets/platforms/pancakebunny.png',
                'token' => 'bunny',
            ],
            [
                'id' => 'pancake',
                'label' => 'Pancake',
                'url' => 'https://pancakeswap.finance/',
                'icon' => '/assets/platforms/pancake.png',
                'token' => 'cake',
            ],
            [
                'id' => 'jetfuel',
                'label' => 'Jetfuel',
                'url' => 'https://jetfuel.finance/',
                'icon' => '/assets/platforms/jetfuel.png',
                'token' => 'fuel',
            ],
            [
                'id' => 'acryptos',
                'label' => 'ACryptoS',
                'url' => 'https://app.acryptos.com/',
                'icon' => '/assets/platforms/acryptos.png',
                'token' => 'acs',
                'tokens' => ['acs', 'acsi'],
            ],
            [
                'id' => 'bakery',
                'label' => 'Bakery',
                'url' => 'https://www.bakeryswap.org/',
                'icon' => '/assets/platforms/bakery.png',
                'token' => 'bake',
            ],
            [
                'id' => 'goose',
                'label' => 'Goose',
                'url' => 'https://www.goosedefi.com/',
                'icon' => '/assets/platforms/goose.png',
                'token' => 'egg',
            ],
            [
                'id' => 'kebab',
                'label' => 'Kebab',
                'url' => 'https://kebabfinance.com',
                'icon' => '/assets/platforms/kebab.png',
                'token' => 'kebab',
            ],
            [
                'id' => 'valuedefi',
                'label' => 'ValueDefi',
                'url' => 'https://bsc.valuedefi.io/#/vfarm',
                'icon' => '/assets/platforms/valuedefi.png',
                'token' => 'vbswap',
            ],
            [
                'id' => 'saltswap',
                'label' => 'SaltSwap',
                'url' => 'https://www.saltswap.finance/',
                'icon' => '/assets/platforms/saltswap.png',
                'token' => 'salt',
            ],
            [
                'id' => 'hyperjump',
                'label' => 'HyperJump',
                'url' => 'https://farm.hyperjump.fi/',
                'icon' => '/assets/platforms/hyperjump.png',
                'token' => 'alloy',
            ],
            [
                'id' => 'apeswap',
                'label' => 'ApeSwap',
                'url' => 'https://apeswap.finance/',
                'icon' => '/assets/platforms/apeswap.png',
                'token' => 'banana',
            ],
            [
                'id' => 'slime',
                'label' => 'Slime',
                'url' => 'https://app.slime.finance/',
                'icon' => '/assets/platforms/slime.png',
                'token' => 'slme',
            ],
            [
                'id' => 'jul',
                'label' => 'JulSwap',
                'url' => 'https://info.julswap.com/',
                'icon' => '/assets/platforms/jul.png',
                'token' => 'juld',
            ],
            [
                'id' => 'space',
                'label' => 'farm.space',
                'url' => 'https://farm.space/',
                'icon' => '/assets/platforms/space.png',
                'token' => 'space',
            ],
            [
                'id' => 'blizzard',
                'label' => 'Blizzard Money',
                'url' => 'https://www.blizzard.money/',
                'icon' => '/assets/platforms/blizzard.png',
                'token' => 'blzd',
            ],
            [
                'id' => 'wault',
                'label' => 'Wault Finance',
                'url' => 'https://app.wault.finance/',
                'icon' => '/assets/platforms/wault.png',
                'token' => 'wex',
            ],
            [
                'id' => 'alpaca',
                'label' => 'Alpaca Finance',
                'url' => 'https://app.alpacafinance.org/',
                'icon' => '/assets/platforms/alpaca.png',
                'token' => 'alpaca',
            ],
            [
                'id' => 'alpha',
                'label' => 'Alpha',
                'url' => 'https://homora-bsc.alphafinance.io/',
                'icon' => '/assets/platforms/alpha.png',
                'token' => 'alpha',
            ],
            [
                'id' => 'mdex',
                'label' => 'MDEX',
                'url' => 'https://bsc.mdex.com/',
                'icon' => '/assets/platforms/mdex.png',
                'token' => 'mdx',
            ],
            [
                'id' => 'polaris',
                'label' => 'Polaris',
                'url' => 'https://app.polarisdefi.io/',
                'icon' => '/assets/platforms/polaris.png',
                'token' => 'polar',
            ],
            [
                'id' => 'cheese',
                'label' => 'Cheesecake',
                'url' => 'https://cheesecakeswap.com/',
                'icon' => '/assets/platforms/cheese.png',
                'token' => 'ccake',
            ],
            [
                'id' => 'swamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'swamp',
            ],
            [
                'id' => 'pandayield',
                'label' => 'PandaYield',
                'url' => 'https://pandayield.com/',
                'icon' => '/assets/platforms/pandayield.png',
                'token' => 'bboo',
            ],
            [
                'id' => 'cafeswap',
                'label' => 'CafeSwap',
                'url' => 'https://app.cafeswap.finance/',
                'icon' => '/assets/platforms/cafeswap.png',
                'token' => 'brew',
            ],
            [
                'id' => 'belt',
                'label' => 'Belt.fi',
                'url' => 'https://belt.fi/',
                'icon' => '/assets/platforms/belt.png',
                'token' => 'belt',
            ],
            [
                'id' => 'panther',
                'label' => 'PantherSwap',
                'url' => 'https://pantherswap.com/?ref=q9gyayn267d5fdgedprkkpzqcjj97eeykj79skaby',
                'icon' => '/assets/platforms/panther.png',
                'token' => 'panther',
            ],
            [
                'id' => 'jetswap',
                'label' => 'JetSwap',
                'url' => 'https://jetswap.finance',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'wings',
            ],
            [
                'id' => 'warden',
                'label' => 'Warden',
                'url' => 'https://farm.wardenswap.finance',
                'icon' => '/assets/platforms/warden.png',
                'token' => 'warden',
            ],
            [
                'id' => 'biswap',
                'label' => 'Biswap',
                'url' => 'https://biswap.org/?ref=f5b2fb48d67b5e8e9f01',
                'icon' => '/assets/platforms/biswap.png',
                'token' => 'bsw',
            ],
            [
                'id' => 'evodefi',
                'label' => 'Evodefi',
                'url' => 'https://evodefi.com?ref=r75LG2b3kxfwOPDaLJ3JF1w2dQdXW2TgPaE',
                'icon' => '/assets/platforms/evodefi.png',
                'token' => 'gen',
            ],
            [
                'id' => 'eleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
            ],
            [
                'id' => 'coinswap',
                'label' => 'Coinswap',
                'url' => 'https://master.coinswap.space/farm?coinswapfriend=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/coinswap.png',
                'token' => 'css',
            ],
            [
                'id' => 'farmhero',
                'label' => 'FarmHero',
                'url' => 'https://bsc.farmhero.io?r=f3rm3rmy',
                'icon' => '/assets/platforms/farmhero.png',
                'token' => 'hero',
            ],
            [
                'id' => 'treedefi',
                'label' => 'Treedefi',
                'url' => 'https://app.treedefi.com',
                'icon' => '/assets/platforms/treedefi.png',
                'token' => 'seed',
            ],
            [
                'id' => 'yieldparrot',
                'label' => 'YieldParrot',
                'url' => 'https://app.yieldparrot.finance',
                'icon' => '/assets/platforms/yieldparrot.png',
                'token' => 'lory',
            ],
            [
                'id' => 'honeyfarm',
                'label' => 'HoneyFarm',
                'url' => 'https://honeyfarm.finance?ref=MHg4OThlOTk2ODFDMjk0NzliODYzMDQyOTJiMDMwNzFDODBBNTc5NDhG',
                'icon' => '/assets/platforms/honeyfarm.png',
                //'token' => 'honey',
                'token' => 'bear',
                'tokens' => [
                    'address' => '0xc3EAE9b061Aa0e1B9BD3436080Dc57D2d63FEdc1',
                    'symbol' => 'bear',
                    'decimals'> 18,
                ]
            ],
            [
                'id' => 'rabbit',
                'label' => 'Rabbit',
                'url' => 'https://rabbitfinance.io',
                'icon' => '/assets/platforms/rabbit.png',
                'token' => 'rabbit',
            ],
            [
                'id' => 'qubit',
                'label' => 'Qubit',
                'url' => 'https://qbt.fi/app',
                'icon' => '/assets/platforms/qubit.png',
                'token' => 'qbt',
            ],
            [
                'id' => 'cream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
            ],
            [
                'id' => 'venus',
                'label' => 'Venus',
                'url' => 'https://app.venus.io/',
                'icon' => '/assets/platforms/venus.png',
                'token' => 'xvs',
            ],
            [
                'id' => 'fortress',
                'label' => 'Fortress',
                'url' => 'https://bsc.fortress.loans/market',
                'icon' => '/assets/platforms/fortress.png',
                'token' => 'fts',
            ],
            [
                'id' => 'fortube',
                'label' => 'ForTube',
                'url' => 'https://for.tube/market/index',
                'icon' => '/assets/platforms/fortube.png',
                'token' => 'for',
            ],
            [
                'id' => 'planet',
                'label' => 'PlanetFinance',
                'url' => 'https://planetfinance.io',
                'icon' => '/assets/platforms/planet.png',
                'token' => 'aqua',
            ],
            [
                'id' => 'ten',
                'label' => 'TEN',
                'url' => 'https://app.ten.finance',
                'icon' => '/assets/platforms/ten.png',
                'token' => 'ten',
            ],
            [
                'id' => 'autoshark',
                'label' => 'AutoShark',
                'url' => 'https://autoshark.finance',
                'icon' => '/assets/platforms/autoshark.png',
                'token' => 'fins',
            ],
            [
                'id' => 'mars',
                'label' => 'Mars',
                'url' => 'https://app.marsecosystem.com/',
                'icon' => '/assets/platforms/mars.png',
                'token' => 'xms',
            ],
            [
                'id' => 'atlantis',
                'label' => 'Atlantis',
                'url' => 'https://atlantis.loans/app',
                'icon' => '/assets/platforms/atlantis.png',
                'token' => 'atl',
            ],
        ];
    }
}