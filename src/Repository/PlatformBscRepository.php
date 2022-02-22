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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'bsc',
        ];
    }

    public function getPlatformChunks(): array
    {
        $chunks = array_map(
            fn($p) => $p['id'],
            $this->getPlatforms()
        );

        $slowGroup = ['alpha', 'alpaca'];

        $chunks = array_diff($chunks, $slowGroup);

        $chunks = array_chunk($chunks, 5);

        $chunks[] = $slowGroup;

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
                'chain' => 'bsc',
                'group' => 'autofarm',
            ],
            [
                'id' => 'beefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/bsc',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'bsc',
                'group' => 'beefy',
            ],
            [
                'id' => 'pancakebunny',
                'label' => 'Bunny',
                'url' => 'https://pancakebunny.finance/',
                'icon' => '/assets/platforms/pancakebunny.png',
                'token' => 'bunny',
                'chain' => 'bsc',
                'group' => 'pancakebunny',
            ],
            [
                'id' => 'pancake',
                'label' => 'Pancake',
                'url' => 'https://pancakeswap.finance/',
                'icon' => '/assets/platforms/pancake.png',
                'token' => 'cake',
                'chain' => 'bsc',
            ],
            [
                'id' => 'jetfuel',
                'label' => 'Jetfuel',
                'url' => 'https://jetfuel.finance/',
                'icon' => '/assets/platforms/jetfuel.png',
                'token' => 'fuel',
                'chain' => 'bsc',
            ],
            [
                'id' => 'acryptos',
                'label' => 'ACryptoS',
                'url' => 'https://app.acryptos.com/',
                'icon' => '/assets/platforms/acryptos.png',
                'token' => 'acs',
                'tokens' => ['acs', 'acsi'],
                'chain' => 'bsc',
            ],
            [
                'id' => 'bakery',
                'label' => 'Bakery',
                'url' => 'https://www.bakeryswap.org/',
                'icon' => '/assets/platforms/bakery.png',
                'token' => 'bake',
                'chain' => 'bsc',
            ],
            [
                'id' => 'goose',
                'label' => 'Goose',
                'url' => 'https://www.goosedefi.com/',
                'icon' => '/assets/platforms/goose.png',
                'token' => 'egg',
                'chain' => 'bsc',
            ],
            [
                'id' => 'saltswap',
                'label' => 'SaltSwap',
                'url' => 'https://www.saltswap.finance/',
                'icon' => '/assets/platforms/saltswap.png',
                'token' => 'salt',
                'chain' => 'bsc',
            ],
            [
                'id' => 'hyperjump',
                'label' => 'HyperJump',
                'url' => 'https://farm.hyperjump.fi/',
                'icon' => '/assets/platforms/hyperjump.png',
                'token' => 'alloy',
                'chain' => 'bsc',
                'group' => 'hyperjump',
            ],
            [
                'id' => 'apeswap',
                'label' => 'ApeSwap',
                'url' => 'https://apeswap.finance/',
                'icon' => '/assets/platforms/apeswap.png',
                'token' => 'banana',
                'chain' => 'bsc',
                'group' => 'apeswap',
            ],
            [
                'id' => 'space',
                'label' => 'farm.space',
                'url' => 'https://farm.space/',
                'icon' => '/assets/platforms/space.png',
                'token' => 'space',
                'chain' => 'bsc',
            ],
            [
                'id' => 'blizzard',
                'label' => 'Blizzard',
                'url' => 'https://www.blizzard.money/',
                'icon' => '/assets/platforms/blizzard.png',
                'token' => 'blzd',
                'chain' => 'bsc',
            ],
            [
                'id' => 'wault',
                'label' => 'Wault',
                'url' => 'https://app.wault.finance/',
                'icon' => '/assets/platforms/wault.png',
                'token' => 'wex',
                'chain' => 'bsc',
                'group' => 'wault',
            ],
            [
                'id' => 'alpaca',
                'label' => 'Alpaca',
                'url' => 'https://app.alpacafinance.org/',
                'icon' => '/assets/platforms/alpaca.png',
                'token' => 'alpaca',
                'chain' => 'bsc',
            ],
            [
                'id' => 'alpha',
                'label' => 'Alpha',
                'url' => 'https://homora-bsc.alphafinance.io/',
                'icon' => '/assets/platforms/alpha.png',
                'token' => 'alpha',
                'chain' => 'bsc',
            ],
            [
                'id' => 'mdex',
                'label' => 'MDEX',
                'url' => 'https://bsc.mdex.com/',
                'icon' => '/assets/platforms/mdex.png',
                'token' => 'mdx',
                'chain' => 'bsc',
            ],
            [
                'id' => 'cheese',
                'label' => 'Cheesecake',
                'url' => 'https://cheesecakeswap.com/',
                'icon' => '/assets/platforms/cheese.png',
                'token' => 'ccake',
                'chain' => 'bsc',
            ],
            [
                'id' => 'swamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'swamp',
                'chain' => 'bsc',
                'group' => 'swamp',
            ],
            [
                'id' => 'pandayield',
                'label' => 'PandaYield',
                'url' => 'https://pandayield.com/',
                'icon' => '/assets/platforms/pandayield.png',
                'token' => 'bboo',
                'chain' => 'bsc',
            ],
            [
                'id' => 'cafeswap',
                'label' => 'CafeSwap',
                'url' => 'https://app.cafeswap.finance/',
                'icon' => '/assets/platforms/cafeswap.png',
                'token' => 'brew',
                'chain' => 'bsc',
                'group' => 'cafeswap',
            ],
            [
                'id' => 'belt',
                'label' => 'Belt.fi',
                'url' => 'https://belt.fi/',
                'icon' => '/assets/platforms/belt.png',
                'token' => 'belt',
                'chain' => 'bsc',
            ],
            [
                'id' => 'panther',
                'label' => 'PantherSwap',
                'url' => 'https://pantherswap.com/?ref=q9gyayn267d5fdgedprkkpzqcjj97eeykj79skaby',
                'icon' => '/assets/platforms/panther.png',
                'token' => 'panther',
                'chain' => 'bsc',
            ],
            [
                'id' => 'jetswap',
                'label' => 'JetSwap',
                'url' => 'https://jetswap.finance',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'wings',
                'chain' => 'bsc',
                'group' => 'jetswap',
            ],
            [
                'id' => 'warden',
                'label' => 'Warden',
                'url' => 'https://farm.wardenswap.finance',
                'icon' => '/assets/platforms/warden.png',
                'token' => 'warden',
                'chain' => 'bsc',
            ],
            [
                'id' => 'biswap',
                'label' => 'Biswap',
                'url' => 'https://biswap.org/?ref=f5b2fb48d67b5e8e9f01',
                'icon' => '/assets/platforms/biswap.png',
                'token' => 'bsw',
                'chain' => 'bsc',
            ],
            [
                'id' => 'evodefi',
                'label' => 'Evodefi',
                'url' => 'https://evodefi.com?ref=r75LG2b3kxfwOPDaLJ3JF1w2dQdXW2TgPaE',
                'icon' => '/assets/platforms/evodefi.png',
                'token' => 'gen',
                'chain' => 'bsc',
            ],
            [
                'id' => 'eleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
                'chain' => 'bsc',
                'group' => 'eleven',
            ],
            [
                'id' => 'coinswap',
                'label' => 'Coinswap',
                'url' => 'https://master.coinswap.space/farm?coinswapfriend=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/coinswap.png',
                'token' => 'css',
                'chain' => 'bsc',
            ],
            [
                'id' => 'farmhero',
                'label' => 'FarmHero',
                'url' => 'https://bsc.farmhero.io?r=f3rm3rmy',
                'icon' => '/assets/platforms/farmhero.png',
                'token' => 'hero',
                'chain' => 'bsc',
                'group' => 'farmhero',
            ],
            [
                'id' => 'treedefi',
                'label' => 'Treedefi',
                'url' => 'https://app.treedefi.com',
                'icon' => '/assets/platforms/treedefi.png',
                'token' => 'seed',
                'chain' => 'bsc',
            ],
            [
                'id' => 'yieldparrot',
                'label' => 'YieldParrot',
                'url' => 'https://app.yieldparrot.finance',
                'icon' => '/assets/platforms/yieldparrot.png',
                'token' => 'lory',
                'chain' => 'bsc',
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
                ],
                'chain' => 'bsc',
            ],
            [
                'id' => 'rabbit',
                'label' => 'Rabbit',
                'url' => 'https://rabbitfinance.io',
                'icon' => '/assets/platforms/rabbit.png',
                'token' => 'rabbit',
                'chain' => 'bsc',
            ],
            [
                'id' => 'cream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
                'chain' => 'bsc',
                'group' => 'cream',
            ],
            [
                'id' => 'venus',
                'label' => 'Venus',
                'url' => 'https://app.venus.io/',
                'icon' => '/assets/platforms/venus.png',
                'token' => 'xvs',
                'chain' => 'bsc',
            ],
            [
                'id' => 'fortress',
                'label' => 'Fortress',
                'url' => 'https://bsc.fortress.loans/market',
                'icon' => '/assets/platforms/fortress.png',
                'token' => 'fts',
                'chain' => 'bsc',
            ],
            [
                'id' => 'fortube',
                'label' => 'ForTube',
                'url' => 'https://for.tube/market/index',
                'icon' => '/assets/platforms/fortube.png',
                'token' => 'for',
                'chain' => 'bsc',
                'group' => 'fortube',
            ],
            [
                'id' => 'planet',
                'label' => 'PlanetFinance',
                'url' => 'https://planetfinance.io',
                'icon' => '/assets/platforms/planet.png',
                'token' => 'aqua',
                'chain' => 'bsc',
            ],
            [
                'id' => 'ten',
                'label' => 'TEN',
                'url' => 'https://app.ten.finance',
                'icon' => '/assets/platforms/ten.png',
                'token' => 'ten',
                'chain' => 'bsc',
            ],
            [
                'id' => 'autoshark',
                'label' => 'AutoShark',
                'url' => 'https://autoshark.finance',
                'icon' => '/assets/platforms/autoshark.png',
                'token' => 'fins',
                'chain' => 'bsc',
            ],
            [
                'id' => 'mars',
                'label' => 'Mars',
                'url' => 'https://app.marsecosystem.com/',
                'icon' => '/assets/platforms/mars.png',
                'token' => 'xms',
                'chain' => 'bsc',
            ],
            [
                'id' => 'atlantis',
                'label' => 'Atlantis',
                'url' => 'https://atlantis.loans/app',
                'icon' => '/assets/platforms/atlantis.png',
                'token' => 'atl',
                'chain' => 'bsc',
                'group' => 'atlantis',
            ],
            [
                'id' => 'synapse',
                'label' => 'Synapse',
                'url' => 'https://synapseprotocol.com/pools',
                'icon' => '/assets/platforms/synapse.png',
                'token' => 'syn',
                'chain' => 'bsc',
                'group' => 'synapse',
            ],
            [
                'id' => 'annex',
                'label' => 'Annex',
                'url' => 'https://app.annex.finance',
                'icon' => '/assets/platforms/annex.png',
                'token' => 'ann',
                'chain' => 'bsc',
                'group' => 'annex',
            ],
            [
                'id' => 'templar',
                'label' => 'Templar',
                'url' => 'https://templar.finance/#/bonds',
                'icon' => '/assets/platforms/templar.png',
                'token' => 'tem',
                'chain' => 'bsc',
                'group' => 'templar',
            ],
            [
                'id' => 'nemesis',
                'label' => 'Nemesis',
                'url' => 'https://rising.nemesisdao.finance/#/bonds',
                'icon' => '/assets/platforms/nemesis.png',
                'token' => 'nms',
                'chain' => 'bsc',
            ],
            [
                'id' => 'hunnydao',
                'label' => 'Hunnydao',
                'url' => 'https://dao.hunny.finance/#/bonds',
                'icon' => '/assets/platforms/hunnydao.png',
                'token' => 'love',
                'chain' => 'bsc',
            ],
            [
                'id' => 'jade',
                'label' => 'Jade',
                'url' => 'https://jadeprotocol.io/#/bonds',
                'icon' => '/assets/platforms/jade.png',
                'token' => 'jade',
                'chain' => 'bsc',
            ],
            [
                'id' => 'unus',
                'label' => 'Unus',
                'url' => 'https://unusdao.finance',
                'icon' => '/assets/platforms/unus.png',
                'token' => 'udo',
                'chain' => 'bsc',
            ],
            [
                'id' => 'theanimal',
                'label' => 'theanimal',
                'url' => 'https://theanimal.farm/referrals/0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/theanimal.png',
                'chain' => 'bsc',
            ],
            /*
            [
                'id' => 'pacoca',
                'label' => 'Pacoca',
                'url' => 'https://pacoca.io/invest',
                'icon' => '/assets/platforms/jade.png',
                'token' => 'jade',
                'chain' => 'bsc',
            ],
            */
        ];
    }
}