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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'fantom',
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
        // $chunks = array_diff($chunks, $middleGroup);

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
                'id' => 'spiritswap',
                'label' => 'SpiritSwap',
                'url' => 'https://app.spiritswap.finance/',
                'icon' => '/assets/platforms/spiritswap.png',
                'token' => 'spirit',
                'chain' => 'fantom',
            ],
            [
                'id' => 'spookyswap',
                'label' => 'SpookySwap',
                'url' => 'https://spookyswap.finance/',
                'icon' => '/assets/platforms/spookyswap.png',
                'token' => 'boo',
                'chain' => 'fantom',
            ],
            [
                'id' => 'liquiddriver',
                'label' => 'LiquidDriver',
                'url' => 'https://www.liquiddriver.finance/',
                'icon' => '/assets/platforms/liquiddriver.png',
                'token' => 'lqdr',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/fantom',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'fantom',
                'group' => 'beefy',
            ],
            [
                'id' => 'fcurve',
                'label' => 'Curve',
                'url' => 'https://ftm.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'crv',
                'chain' => 'fantom',
                'group' => 'pcurve',
            ],
            [
                'id' => 'frankenstein',
                'label' => 'Frankenstein',
                'url' => 'https://frankenstein.finance/?ref=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/frankenstein.png',
                'token' => 'frank',
                'chain' => 'fantom',
            ],
            [
                'id' => 'ester',
                'label' => 'Ester',
                'url' => 'https://app.ester.finance/',
                'icon' => '/assets/platforms/ester.png',
                'token' => 'est',
                'chain' => 'fantom',
            ],
            [
                'id' => 'reaper',
                'label' => 'Reaper',
                'url' => 'https://www.reaper.farm/',
                'icon' => '/assets/platforms/reaper.png',
                'token' => 'unknown',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fcream',
                'label' => 'Cream',
                'url' => 'https://app.cream.finance/',
                'icon' => '/assets/platforms/cream.png',
                'token' => 'cream',
                'chain' => 'fantom',
                'group' => 'cream',
            ],
            [
                'id' => 'scream',
                'label' => 'Scream',
                'url' => 'https://scream.sh/',
                'icon' => '/assets/platforms/scream.png',
                'token' => 'scream',
                'chain' => 'fantom',
            ],
            [
                'id' => 'tarot',
                'label' => 'Tarot',
                'url' => 'https://www.tarot.to/',
                'icon' => '/assets/platforms/tarot.png',
                'token' => 'tarot',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fwaka',
                'label' => 'Waka',
                'url' => 'https://waka.finance/',
                'icon' => '/assets/platforms/waka.png',
                'token' => 'waka',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fhyperjump',
                'label' => 'HyperJump',
                'url' => 'https://ftm.hyperjump.app/farms',
                'icon' => '/assets/platforms/hyperjump.png',
                'token' => 'ori',
                'chain' => 'fantom',
                'group' => 'hyperjump',
            ],
            [
                'id' => 'fautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/fantom/',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'fauto',
                'chain' => 'fantom',
                'group' => 'autofarm',
            ],
            [
                'id' => 'feleven',
                'label' => 'eleven',
                'url' => 'https://eleven.finance',
                'icon' => '/assets/platforms/eleven.png',
                'token' => 'ele',
                'chain' => 'fantom',
                'group' => 'eleven',
            ],
            [
                'id' => 'fjetswap',
                'label' => 'JetSwap',
                'url' => 'https://fantom.jetswap.finance/',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'fwings',
                'chain' => 'fantom',
                'group' => 'jetswap',
            ],
            [
                'id' => 'paintswap',
                'label' => 'PaintSwap',
                'url' => 'https://paintswap.finance/',
                'icon' => '/assets/platforms/paintswap.png',
                'token' => 'brush',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fswamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/fantom/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'fswamp',
                'chain' => 'fantom',
                'group' => 'swamp',
            ],
            [
                'id' => 'beethovenx',
                'label' => 'Beethovenx',
                'url' => 'https://app.beethovenx.io/#/',
                'icon' => '/assets/platforms/beethovenx.png',
                'token' => 'beets',
                'chain' => 'fantom',
            ],
            [
                'id' => 'robovault',
                'label' => 'RoboVault',
                'url' => 'https://www.robo-vault.com/',
                'icon' => '/assets/platforms/robovault.png',
                'chain' => 'fantom',
            ],
            [
                'id' => 'morpheus',
                'label' => 'Morpheus',
                'url' => 'https://morpheusswap.app/',
                'icon' => '/assets/platforms/morpheus.png',
                'token' => 'morph',
                'chain' => 'fantom',
            ],
            [
                'id' => 'geist',
                'label' => 'Geist',
                'url' => 'https://geist.finance/markets',
                'icon' => '/assets/platforms/geist.png',
                'token' => 'geist',
                'chain' => 'fantom',
            ],
            [
                'id' => 'grim',
                'label' => 'Grim',
                'url' => 'https://app.grim.finance/',
                'icon' => '/assets/platforms/grim.png',
                'chain' => 'fantom',
            ],
            [
                'id' => 'zoocoin',
                'label' => 'Zoocoin',
                'url' => 'https://dex.zoocoin.cash/',
                'icon' => '/assets/platforms/zoocoin.png',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fsynapse',
                'label' => 'Synapse',
                'url' => 'https://synapseprotocol.com/pools',
                'icon' => '/assets/platforms/synapse.png',
                'token' => 'syn',
                'chain' => 'fantom',
                'group' => 'synapse',
            ],
            [
                'id' => 'hectordao',
                'label' => 'Hector',
                'url' => 'https://app.hectordao.com/#/dashboard',
                'icon' => '/assets/platforms/hectordao.png',
                'token' => 'hec',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fantohm',
                'label' => 'FantOHM',
                'url' => 'https://app.hectordao.com/#/dashboard',
                'icon' => '/assets/platforms/fantohm.png',
                'token' => 'fhm',
                'chain' => 'fantom',
            ],
            [
                'id' => 'hundred',
                'label' => 'Hundred',
                'url' => 'https://hundred.finance',
                'icon' => '/assets/platforms/hundred.png',
                'token' => 'hnd',
                'chain' => 'fantom',
                'group' => 'hundred',
            ],
            [
                'id' => 'revenant',
                'label' => 'Revenant',
                'url' => 'https://revenant.finance/creditum',
                'icon' => '/assets/platforms/revenant.png',
                'token' => 'credit',
                'chain' => 'fantom',
            ],
            [
                'id' => 'soulswap',
                'label' => 'SoulSwap',
                'url' => 'https://exchange.soulswap.finance/farms',
                'icon' => '/assets/platforms/soulswap.png',
                'token' => 'soul',
                'chain' => 'fantom',
            ],
            [
                'id' => 'luxor',
                'label' => 'Luxor',
                'url' => 'https://app.luxor.money',
                'icon' => '/assets/platforms/luxor.png',
                'token' => 'lux',
                'chain' => 'fantom',
            ],
            [
                'id' => 'knightswap',
                'label' => 'KnightSwap',
                'url' => 'https://dark.knightswap.financial',
                'icon' => '/assets/platforms/knightswap.png',
                'token' => 'dknight',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fyearn',
                'label' => 'yearn',
                'url' => 'https://yearn.finance/#/vaults',
                'icon' => '/assets/platforms/yearn.png',
                'token' => 'yfi',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fmarket',
                'label' => 'Market',
                'url' => 'https://fantom.market.xyz/',
                'icon' => '/assets/platforms/market.png',
                'chain' => 'fantom',
                'group' => 'market',
            ],
            [
                'id' => 'oxdao',
                'label' => '0xDAO',
                'url' => 'https://www.oxdao.fi',
                'icon' => '/assets/platforms/oxdao.png',
                'chain' => 'fantom',
            ],
            [
                'id' => 'fsushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
                'chain' => 'fantom',
                'group' => 'psushi',
            ],
            [
                'id' => 'protofi',
                'label' => 'Protofi',
                'url' => 'https://fantomdex.protofi.app',
                'icon' => '/assets/platforms/protofi.png',
                'token' => 'proto',
                'chain' => 'fantom',
            ],
            [
                'id' => 'wigoswap',
                'label' => 'Wigoswap',
                'url' => 'https://wigoswap.io',
                'icon' => '/assets/platforms/wigoswap.png',
                'token' => 'wigo',
                'chain' => 'fantom',
            ],
        ];
    }
}